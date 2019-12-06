<?php

namespace app\common\validate;

use think\Validate;

class UserShuoshuo extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['user_id', 'number|max:11', '用户ID必须是数字|用户ID格式不正确'],
        ['desc', 'max:150', '描述不能超过150个字符'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'desc', 'add_time'],
        'edit' => ['user_id', 'desc'],
        'del' => ['id'],
    ];
}