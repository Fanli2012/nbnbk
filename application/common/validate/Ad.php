<?php

namespace app\common\validate;

use think\Validate;

class Ad extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['name', 'require|max:60', '名称不能为空|名称不能超过60个字符'],
        ['description', 'max:255', '描述不能超过255个字符'],
        ['flag', 'max:30', '标识不能超过30个字符'],
        ['is_expire', 'in:0,1,2', '0永不过期'],
        ['start_time', 'number|egt:0', '投放开始时间格式不正确|投放开始时间格式不正确'],
        ['end_time', 'number|egt:start_time', '投放结束时间格式不正确|投放结束时间格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['name', 'description', 'flag', 'is_expire', 'start_time', 'end_time', 'add_time'],
        'edit' => ['name', 'description', 'flag', 'is_expire', 'start_time', 'end_time', 'add_time'],
        'del' => ['id'],
    ];
}