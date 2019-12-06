<?php

namespace app\common\validate;

use think\Validate;

class Keyword extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:30', '内链关键词不能为空|内链关键词不能超过30个字符'],
        ['url', 'require|max:150', '跳转链接不能为空|跳转链接不能超过150个字符'],
    ];

    protected $scene = [
        'add' => ['name', 'url'],
        'edit' => ['name', 'url'],
        'del' => ['id'],
    ];
}