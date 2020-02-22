<?php

namespace app\common\validate;

use think\Validate;

class Access extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number', 'ID不能为空|ID必须是数字'],
        ['role_id', 'require|number|gt:0', '角色ID不能为空|角色ID必须是数字|角色ID格式不正确'],
        ['menu_id', 'require|number|gt:0', '菜单ID不能为空|菜单ID必须是数字|菜单ID格式不正确'],
    ];

    protected $scene = [
        'add' => ['role_id', 'menu_id'],
        'edit' => ['role_id', 'menu_id'],
        'del' => ['id'],
    ];
}