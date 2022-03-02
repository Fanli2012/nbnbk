<?php
// 入口文件

// 禁止代理IP访问
empty($_SERVER['HTTP_VIA']) or exit('Access Denied');

/**
 * 检测请求方式，除了get和post之外拦截下来并写日志。
 */
if ($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'GET') {
	sleep(15);
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	exit;
}

function _waf_defense()
{
	// 请求头
	$headers = array();
	foreach ($_SERVER as $key => $value) {
		if (substr($key, 0, 5) == 'HTTP_') {
			$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
		}
	}
	$http_cookie = '';
	if (!empty($_SERVER['HTTP_COOKIE'])) {
		$http_cookie = $_SERVER['HTTP_COOKIE'];
	}
	$str = urldecode($_SERVER['REQUEST_URI']) . urldecode($http_cookie) . urldecode(file_get_contents('php://input')) . implode('', $headers);

	if (preg_match("/base64_decode|eval\(|assert\(/i", $str) || preg_match("/select\b|insert\b|update\b|drop\b|delete\b|dumpfile\b|outfile\b|load_file|rename\b|floor\(|extractvalue|updatexml|name_const|multipoint\(/i", $str)) {
		exit;
	}
	$rules = [
		'\.\./', //禁用包含 ../ 的参数
		'\<\?', //禁止php脚本出现
		'\s*or\s+.*=.*', //匹配' or 1=1 ,防止sql注入
		'select([\s\S]*?)(from|limit)', //防止sql注入
		'(?:(union([\s\S]*?)select))', //防止sql注入
		'having|updatexml|extractvalue', //防止sql注入
		'sleep\((\s*)(\d*)(\s*)\)', //防止sql盲注
		'benchmark\((.*)\,(.*)\)', //防止sql盲注
		'base64_decode\(', //防止sql变种注入
		'(?:from\W+information_schema\W)', //防止sql注入
		'(?:(?:current_)user|database|schema|connection_id)\s*\(', //防止sql注入
		'(?:etc\/\W*passwd)', //防止窥探linux用户信息
		'into(\s+)+(?:dump|out)file\s*', //禁用mysql导出函数
		'group\s+by.+\(', //防止sql注入
		'(?:define|eval|file_get_contents|include|require|require_once|shell_exec|phpinfo|system|passthru|preg_\w+|execute|echo|print|print_r|var_dump|(fp)open|alert|showmodaldialog)\(', //禁用webshell相关某些函数
		'(gopher|doc|php|glob|file|phar|zlib|ftp|ldap|dict|ogg|data)\:\/', //防止一些协议攻击
		'\$_(GET|post|cookie|files|session|env|phplib|GLOBALS|SERVER)\[', //禁用一些内置变量,建议自行修改
		'\<(iframe|script|body|img|layer|div|meta|style|base|object|input)', //防止xss标签植入
		'(onmouseover|onerror|onload|onclick)\=', //防止xss事件植入
		'\|\|.*(?:ls|pwd|whoami|ll|ifconfog|ipconfig|&&|chmod|cd|mkdir|rmdir|cp|mv)', //防止执行shell
		'\s*and\s+.*=.*' //匹配 and 1=1
	];

	foreach ($rules as $rule) {
		if (preg_match('^' . $rule . '^i', $str)) {
			exit;
		}
	}
	return true;
}

if (strpos($_SERVER['REQUEST_URI'], 'fladmin') !== false) {

} else {
	//_waf_defense();
}

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';