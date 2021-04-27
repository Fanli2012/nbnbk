<?php

namespace app\common\validate;

use think\Validate;

class Sysconfig extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['varname', 'require|max:30|checkName', '变量名不能为空|变量名不能超过30个字符'],
        ['info', 'require|max:100', '变量值不能为空|变量值不能超过100个字符'],
        ['value', 'require', '变量说明不能为空'],
    ];

    protected $scene = [
        'add' => ['varname', 'info'],
        'edit' => ['varname', 'info'],
        'del' => ['id'],
    ];

    /**
     * 变量名验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkName($value, $rule, $data, $field)
    {
        if (preg_match("/^CMS_[A-Z_]+$/", $value)) {
            return true;
        }

        return '变量名格式不正确';
    }

}