<?php

namespace app\common\logic;

use think\Loader;
use app\common\lib\ReturnData;

class ToolLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    //SMTP发送邮件
    public function smtp_sendmail($content)
    {
        $text = date('Y-m-d H:i:s') . '服务器挂了，有效期30分钟。<br>' . $content;
		//发送邮件
		$smtpserver = 'smtp.sina.com';//SMTP服务器
		$smtpserverport = 25;//SMTP服务器端口
		$smtpusermail = '1feng2010@sina.com';//SMTP服务器的用户邮箱
		$smtpemailto = '277023115@qq.com';//发送给谁
		$smtpuser = "1feng2010@sina.com";//SMTP服务器的用户帐号
		$smtppass = "seo123456";//SMTP服务器的用户密码
		$mailtitle = date('Y-m-d H:i:s') . '验证码';//邮件主题
		$mailcontent = $text;//邮件内容
		$mailtype = 'HTML';//邮件格式(HTML/TXT),TXT为文本邮件
		$smtp = new \app\common\lib\Smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
		$smtp->debug = false;//是否显示发送的调试信息
		$state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
		if ($state == '') {
			return '对不起，邮件发送失败！请检查邮箱填写是否有误。';
		}

		return true;
    }
}