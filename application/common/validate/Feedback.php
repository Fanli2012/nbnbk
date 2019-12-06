<?php

namespace app\common\validate;

use think\Validate;

class Feedback extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number', 'ID不能为空|ID必须是数字'],
        ['content', 'require', '意见反馈内容不能为空'],
        ['title', 'max:150', '标题不能为空|标题不能超过150个字符'],
        ['user_id', 'number', '用户ID必须是数字'],
    ];

    protected $scene = [
        'add' => ['content', 'title', 'user_id'],
        'del' => ['id'],
    ];
}