<?php

namespace app\common\lib;

/**
 * 异步实现类
 */
class Async
{
    public function __construct()
    {

    }

    /**
     * 通过CURL将数据POST请求
     * @param array $data 传输数据数组
     * @param string $url 接收的URL
     * @return array
     */
    function curl_async($data, $url)
    {
        $ch = curl_init();
        try {
            $curl_opt = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_TIMEOUT => 1
            );
            curl_setopt_array($ch, $curl_opt);
            $result['response'] = curl_exec($ch);//抓取URL并把它传递给浏览器 成功时返回 TRUE， 失败时返回 FALSE
            $result['httpCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($result['response']) {
                $data = ['code' => 0, 'msg' => '发送成功', 'data' => $result];
            } else {
                $data = ['code' => 1, 'msg' => '发送失败', 'data' => $result];
            }
        } catch (Exception $e) {
			// var_dump(curl_error($ch));//显示错误原因
            $data = ['code' => 1, 'msg' => '发送失败', 'data' => curl_error($ch)];
        }
        curl_close($ch);
        return $data;
    }

    /**
     * 远程GET请求（不获取内容）函数
     * @param string $host 域名
     * @param string $url 路径
     * @param array $param 传输数据数组
     * @return array
     */
    function sock_get($host, $url, $param)
    {
        $port = parse_url($url, PHP_URL_PORT);//获取端口
        $port = $port ? $port : 80;
        $scheme = parse_url($url, PHP_URL_SCHEME);//获取协议 http https
        $path = parse_url($url, PHP_URL_PATH);//获取域名
        $query = isset($param) ? http_build_query($param) : '';//获取路径

        if ($scheme == 'https') {
            $host = 'ssl://' . $host;
        }

        $fp = fsockopen($host, $port, $error_code, $error_msg, 1);
        if (!$fp) {
            return array('error_code' => $error_code, 'error_msg' => $error_msg);
        } else {
            stream_set_blocking($fp, true);//开启非阻塞模式
            stream_set_timeout($fp, 1);//设置超时
            $header = "GET $path" . "?" . "$query" . " HTTP/1.1\r\n";
            $header .= "Host: $host\r\n";
            $header .= "Connection: close\r\n\r\n";//长连接关闭
            fwrite($fp, $header);
            usleep(2000); // 延时，防止在nginx服务器上无法执行成功
            fclose($fp);
            $data = ['code' => 0, 'msg' => '发送成功', 'data' => ''];
            return $data;
        }
    }

    /**
     * 远程POST请求（不获取内容）函数
     * @param string $host 域名
     * @param string $url 路径
     * @param array $param 传输数据数组
     * @return array
     */
    function sock_post($host, $url, $param)
    {
        $port = parse_url($url, PHP_URL_PORT);//获取端口
        $port = $port ? $port : 80;
        $scheme = parse_url($url, PHP_URL_SCHEME);//获取协议 http https
        $path = parse_url($url, PHP_URL_PATH);//获取域名
        $query = isset($param) ? http_build_query($param) : '';//获取路径

        if ($scheme == 'https') {
            $host = 'ssl://' . $host;
        }

        $fp = fsockopen($host, $port, $error_code, $error_msg, 1);

        if (!$fp) {
			return ['code' => 1, 'msg' => $error_msg, 'data' => $error_code];
        }
        stream_set_blocking($fp, true);//开启非阻塞模式
        stream_set_timeout($fp, 1);//设置超时
        $header = "POST $path HTTP/1.1\r\n";
        $header .= "Host: $host\r\n";
        $header .= "Content-length:" . strlen(trim($query)) . "\r\n";
        $header .= "Content-type:application/x-www-form-urlencoded\r\n";
        $header .= "Connection: close\r\n\r\n";//长连接关闭
        $header .= "\r\n";
        $header .= $query . "\r\n";
        fwrite($fp, $header);
        //连接主动断开时，线上proxy层没有及时把请求发给上游
        usleep(2000); // 延时，防止在nginx服务器上无法执行成功
        fclose($fp);
        $data = ['code' => 0, 'msg' => '发送成功', 'data' => ''];
        return $data;
    }

}