<?php
// +----------------------------------------------------------------------
// | 表单
// +----------------------------------------------------------------------
namespace app\common\lib;

class Form
{
	public static function post_check($str)
	{
		// 判断magic_quotes_gpc是否为打开
		if (!get_magic_quotes_gpc()) {
			$str = addslashes($str); // 进行magic_quotes_gpc没有打开的情况对提交数据的过滤
		}
		$str = str_replace("_", "\_", $str); // 把 '_'过滤掉
		$str = str_replace("%", "\%", $str); // 把' % '过滤掉
		$str = nl2br($str); // 回车转换
		$str= htmlspecialchars($str); // html标记转换
		return $str;
	}
}