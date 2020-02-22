<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserPoint extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID不能为空|用户ID必须是数字|用户ID格式不正确'],
        ['point', 'require|number|max:11', '积分不能为空|积分必须是数字|积分格式不正确'],
        ['desc', 'require|max:100', '描述不能为空|描述格式不正确'],
        ['user_point', 'number|max:11', '每次增减后的积分必须是数字|每次增减后的积分格式不正确'],
        ['type', 'require|in:0,1', '类型不能为空|类型：0增加,1减少'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'point', 'desc', 'type'],
        'edit' => ['user_id', 'point', 'desc', 'type'],
        'del' => ['user_id'],
    ];
}