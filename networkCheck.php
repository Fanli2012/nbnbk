<?php
//* * * * * /usr/local/php/bin/php /var/www/qiyexcx/networkCheck.php //每分钟执行一次
//* * * * * /usr/local/php/bin/php /var/www/qiyexcx/networkCheck.php //每小时执行一次
//检查网络状况
function network_check($url)
{
    $agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:42.0) Gecko/20100101 Firefox/42.0";
    //curl_init-初始化一个curl会话
    $ch = curl_init();
    //curl_setopt — 为一个curl设置会话参数
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    //curl_exec —执行一个curl会话
    $page = curl_exec($ch);
    //curl_getinfo — 获取一个curl连接资源句柄的信息
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //curl_close()函数的作用是关闭一个curl会话，唯一的参数是curl_init()函数返回的句柄。
    curl_close($ch);
    if ($httpcode >= 200 && $httpcode < 400) {
        return true;
    }
    return false;
}

//发送短信
function send_sms()
{
    $statusStr = array(
        "0" => "短信发送成功",
        "-1" => "参数不全",
        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30" => "密码错误",
        "40" => "账号不存在",
        "41" => "余额不足",
        "42" => "帐户已过期",
        "43" => "IP地址限制",
        "50" => "内容含有敏感词"
    );
    $smsapi = "http://api.smsbao.com/";
    $user = "xmzjt2017"; //短信平台帐号
    $pass = md5("zhengkai123"); //短信平台密码
    $content = "服务器挂了";//要发送的短信内容
    $phone = "15280719357";//要发送短信的手机号码
    $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
    $result = file_get_contents($sendurl);
    return $result;
    echo $statusStr[$result];
}

class Smtp
{
    /* Public Variables */
    var $smtp_port;//SMTP服务器端口
    var $time_out;//
    var $host_name;//
    var $log_file;
    var $relay_host;//SMTP服务器
    var $debug;//是否显示发送的调试信息
    var $auth;//true是表示使用身份验证,否则不使用身份验证
    var $user;//SMTP服务器的用户帐号
    var $pass;//SMTP服务器的用户密码

    /* Private Variables */
    var $sock;

    /* Constractor */

    function __construct($relay_host = '', $smtp_port = 25, $auth = false, $user, $pass)
    {
        $this->debug = FALSE;
        $this->smtp_port = $smtp_port;
        $this->relay_host = $relay_host;
        $this->time_out = 30; //is used in fsockopen() 
        $this->auth = $auth;//auth
        $this->user = $user;
        $this->pass = $pass;
        $this->host_name = 'localhost'; //is used in HELO command 
        $this->log_file = '';
        $this->sock = FALSE;
    }

    /*获取要抓取的列表的URL地址
     *
     * @param $to 发送给谁
     * @param $from SMTP服务器的用户邮箱
     * @param $subject 邮件主题
     * @param $body 邮件内容
     * @param $mailtype 邮件格式(HTML/TXT),TXT为文本邮件
     *
     * @return array 返回列表URL
     */
    function sendmail($to, $from, $subject = '', $body = '', $mailtype, $cc = '', $bcc = '', $additional_headers = '')
    {
        $mail_from = $this->get_address($this->strip_comment($from));
        $body = preg_replace("/(^|(\r\n))(\.)/", "\1.\3", $body);
        $header = "MIME-Version:1.0\r\n";

        if ($mailtype == "HTML") {
            $header .= "Content-Type:text/html\r\n";
        }

        $header .= "To: " . $to . "\r\n";

        if ($cc != "") {
            $header .= "Cc: " . $cc . "\r\n";
        }

        $header .= "From: $from<" . $from . ">\r\n";
        $header .= "Subject: " . $subject . "\r\n";
        $header .= $additional_headers;
        $header .= "Date: " . date("r") . "\r\n";
        $header .= "X-Mailer:By Redhat (PHP/" . phpversion() . ")\r\n";

        list($msec, $sec) = explode(" ", microtime());

        $header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $mail_from . ">\r\n";

        $TO = explode(",", $this->strip_comment($to));

        if ($cc != "") {
            $TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
        }

        if ($bcc != "") {
            $TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
        }

        $sent = TRUE;

        foreach ($TO as $rcpt_to) {
            $rcpt_to = $this->get_address($rcpt_to);

            if (!$this->smtp_sockopen($rcpt_to)) {
                $this->log_write("Error: Cannot send email to " . $rcpt_to . "\n");
                $sent = FALSE;
                continue;
            }

            if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body)) {
                $this->log_write("E-mail has been sent to <" . $rcpt_to . ">\n");
            } else {
                $this->log_write("Error: Cannot send email to <" . $rcpt_to . ">\n");
                $sent = FALSE;
            }

            fclose($this->sock);
            $this->log_write("Disconnected from remote host\n");
        }

        return $sent;
    }

    /* Private Functions */

    function smtp_send($helo, $from, $to, $header, $body = "")
    {
        if (!$this->smtp_putcmd("HELO", $helo)) {
            return $this->smtp_error("sending HELO command");
        }

        //auth

        if ($this->auth) {
            if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->user))) {
                return $this->smtp_error("sending HELO command");
            }

            if (!$this->smtp_putcmd("", base64_encode($this->pass))) {
                return $this->smtp_error("sending HELO command");
            }
        }

        if (!$this->smtp_putcmd("MAIL", "FROM:<" . $from . ">")) {
            return $this->smtp_error("sending MAIL FROM command");
        }

        if (!$this->smtp_putcmd("RCPT", "TO:<" . $to . ">")) {
            return $this->smtp_error("sending RCPT TO command");
        }

        if (!$this->smtp_putcmd("DATA")) {
            return $this->smtp_error("sending DATA command");
        }

        if (!$this->smtp_message($header, $body)) {
            return $this->smtp_error("sending message");
        }

        if (!$this->smtp_eom()) {
            return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
        }

        if (!$this->smtp_putcmd("QUIT")) {
            return $this->smtp_error("sending QUIT command");
        }

        return TRUE;
    }

    function smtp_sockopen($address)
    {
        if ($this->relay_host == "") {
            return $this->smtp_sockopen_mx($address);
        } else {
            return $this->smtp_sockopen_relay();
        }
    }

    function smtp_sockopen_relay()
    {
        $this->log_write("Trying to " . $this->relay_host . ":" . $this->smtp_port . "\n");
        $this->sock = @fsockopen($this->relay_host, $this->smtp_port, $errno, $errstr, $this->time_out);

        if (!($this->sock && $this->smtp_ok())) {
            $this->log_write("Error: Cannot connenct to relay host " . $this->relay_host . "\n");
            $this->log_write("Error: " . $errstr . " (" . $errno . ")\n");

            return FALSE;
        }

        $this->log_write("Connected to relay host " . $this->relay_host . "\n");
        return TRUE;;
    }

    function smtp_sockopen_mx($address)
    {
        $domain = preg_replace("/^.+@([^@]+)$/", "\1", $address);

        if (!@getmxrr($domain, $MXHOSTS)) {
            $this->log_write("Error: Cannot resolve MX \"" . $domain . "\"\n");
            return FALSE;
        }

        foreach ($MXHOSTS as $host) {
            $this->log_write("Trying to " . $host . ":" . $this->smtp_port . "\n");
            $this->sock = @fsockopen($host, $this->smtp_port, $errno, $errstr, $this->time_out);

            if (!($this->sock && $this->smtp_ok())) {
                $this->log_write("Warning: Cannot connect to mx host " . $host . "\n");
                $this->log_write("Error: " . $errstr . " (" . $errno . ")\n");
                continue;
            }

            $this->log_write("Connected to mx host " . $host . "\n");
            return TRUE;
        }

        $this->log_write("Error: Cannot connect to any mx hosts (" . implode(", ", $MXHOSTS) . ")\n");
        return FALSE;
    }

    function smtp_message($header, $body)
    {
        fputs($this->sock, $header . "\r\n" . $body);
        $this->smtp_debug("> " . str_replace("\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> "));
        return TRUE;
    }

    function smtp_eom()
    {
        fputs($this->sock, "\r\n.\r\n");
        $this->smtp_debug(". [EOM]\n");
        return $this->smtp_ok();
    }

    function smtp_ok()
    {
        $response = str_replace("\r\n", "", fgets($this->sock, 512));
        $this->smtp_debug($response . "\n");

        if (!preg_match("/^[23]/", $response)) {
            fputs($this->sock, "QUIT\r\n");
            fgets($this->sock, 512);
            $this->log_write("Error: Remote host returned \"" . $response . "\"\n");
            return FALSE;
        }

        return TRUE;
    }

    function smtp_putcmd($cmd, $arg = "")
    {
        if ($arg != "") {
            if ($cmd == "") $cmd = $arg;
            else $cmd = $cmd . " " . $arg;
        }

        fputs($this->sock, $cmd . "\r\n");
        $this->smtp_debug("> " . $cmd . "\n");
        return $this->smtp_ok();
    }

    function smtp_error($string)
    {
        $this->log_write("Error: Error occurred while " . $string . ".\n");
        return FALSE;
    }

    function log_write($message)
    {
        $this->smtp_debug($message);

        if ($this->log_file == "") {
            return TRUE;
        }

        $message = date("M d H:i:s ") . get_current_user() . "[" . getmypid() . "]: " . $message;

        if (!@file_exists($this->log_file) || !($fp = @fopen($this->log_file, "a"))) {
            $this->smtp_debug("Warning: Cannot open log file \"" . $this->log_file . "\"\n");
            return FALSE;;
        }

        flock($fp, LOCK_EX);
        fputs($fp, $message);
        fclose($fp);

        return TRUE;
    }

    function strip_comment($address)
    {
        $comment = "/\([^()]*\)/";

        while (preg_match($comment, $address)) {
            $address = preg_replace($comment, "", $address);
        }

        return $address;
    }

    function get_address($address)
    {
        $address = preg_replace("/([ \t\r\n])+/", "", $address);
        $address = preg_replace("/^.*<(.+)>.*$/", "\1", $address);

        return $address;
    }

    function smtp_debug($message)
    {
        if ($this->debug) {
            echo $message;
        }
    }
}

//函数参数为要检查的网站的网址路径
if (network_check("http://fc.xyabb.com")) {
    echo "Website OK";
} else {
	$text = date('Y-m-d H:i') . '服务器挂了，有效期30分钟。';
	//发送邮件
	$smtpserver = 'smtp.sina.com';//SMTP服务器
	$smtpserverport = 25;//SMTP服务器端口
	$smtpusermail = '1feng2010@sina.com';//SMTP服务器的用户邮箱
	$smtpemailto = '277023115@qq.com';//发送给谁
	$smtpuser = "1feng2010@sina.com";//SMTP服务器的用户帐号
	$smtppass = "seo123456";//SMTP服务器的用户密码
	$mailtitle = date('Y-m-d H:i:s').'验证码';//邮件主题
	$mailcontent = $text;//邮件内容
	$mailtype = 'HTML';//邮件格式(HTML/TXT),TXT为文本邮件
	$smtp = new Smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
	$smtp->debug = false;//是否显示发送的调试信息
	$state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
	if ($state == '') {
		return '对不起，邮件发送失败！请检查邮箱填写是否有误。';
	} else {
		send_sms();
	}
	
    echo "Website DOWN";
}
