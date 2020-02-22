<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class UserReferralCommission extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID不能为空|用户ID必须是数字|用户ID格式不正确'],
        ['commission_total', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '累计佣金不能为空|累计佣金格式不正确'],
        ['commission_available', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '可提取佣金不能为空|可提取佣金格式不正确'],
        ['commission_withdraw', 'require|regex:/^\d{0,10}(\.\d{0,2})?$/', '已提取佣金不能为空|已提取佣金格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['user_id', 'commission_total', 'commission_available', 'commission_withdraw', 'add_time'],
        'edit' => ['user_id', 'commission_total', 'commission_available', 'commission_withdraw'],
        'del' => ['user_id'],
    ];
}