<?php

namespace app\index\behavior;

use think\Db;

class Operation
{
    public function run()
    {
        $this->action_begin();
    }

    public function action_begin()
    {
        // 添加管理员操作记录
		$this->operation_log_add();
    }

	// 添加管理员操作记录
	public function operation_log_add()
    {
		$time = time();
		// 记录操作
        $data['type'] = 2;
        $data['ip'] = request()->ip();
        $data['url'] = mb_strcut(request()->url(), 0, 255, 'UTF-8');
        $data['http_method'] = request()->method();
        $data['domain_name'] = mb_strcut($_SERVER['SERVER_NAME'], 0, 60, 'UTF-8');
        if ($data['http_method'] != 'GET') { $data['content'] = mb_strcut(json_encode(input(), JSON_UNESCAPED_SLASHES), 0, 255, 'UTF-8'); }
		if (!empty($_SERVER['HTTP_REFERER'])) { $data['http_referer'] = mb_strcut($_SERVER['HTTP_REFERER'], 0, 255, 'UTF-8'); }
        $data['add_time'] = $time;
        logic('Log')->add($data);
    }

    /**
     * 下划线转驼峰
     * 思路:
     * step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
     * step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符
     */
    public function camelize($uncamelized_words, $separator = '_')
    {
        $uncamelized_words = $separator . str_replace($separator, ' ', strtolower($uncamelized_words));
        return ltrim(str_replace(' ', '', ucwords($uncamelized_words)), $separator);
    }

    /**
     * 驼峰命名转下划线命名
     * 思路:
     * 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     */
    public function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}