<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Validator;

class Feedback extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['title', 'max:150', '标题不能为空|标题不能超过150个字符'],
        ['user_id', 'number|max:11', '用户ID必须是数字|用户ID格式不正确'],
        ['content', 'require', '意见反馈内容不能为空'],
        ['mobile', 'isMobile', '手机号格式不正确'],
        ['type', 'max:20', '意见反馈类型不能超过20个字符'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['title', 'user_id', 'content', 'mobile', 'type'],
        'del' => ['id'],
    ];

    // 手机号校验
    protected function isMobile($value, $rule, $data)
    {
        if (empty($value)) {
            return '手机号不能为空';
        }

        if (Validator::isMobile($value)) {
            return true;
        }

        return '手机号格式不正确';
    }
}