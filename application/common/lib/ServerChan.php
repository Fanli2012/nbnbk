<?php

namespace app\common\lib;

//Server酱，https://sc.ftqq.com/
class ServerChan
{
	public static function send($text, $desp = '', $key = 'SCU8682Ta310024b3d69c2d76ee8d23c7c6a1146592925b9bac9d')
	{
		return curl_request('https://sc.ftqq.com/' . $key . '.send', ['text' => $text], 'GET');
		// [errno] => 0 [errmsg] => success [dataset] => done
	}
}
//file_get_contents('https://sc.ftqq.com/SCU8682Ta310024b3d69c2d76ee8d23c7c6a1146592925b9bac9d.send?text=' . urlencode('主人服务器又挂掉啦~'));