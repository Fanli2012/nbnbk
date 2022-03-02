<?php

namespace app\common\validate;

use think\Validate;

class Log extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|gt:0', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['ip', 'require|max:15|ip', 'IP不能为空|IP不能超过15个字符|IP格式不正确'],
        ['content', 'max:250', '操作内容不能超过255个字符'],
        ['login_name', 'max:30', '登录名称不能为空|登录名称不能超过30个字符'],
        ['login_id', 'number|egt:0', '登录ID不能为空|登录ID必须是数字|登录ID格式不正确'],
        ['url', 'require|max:255', 'URL不能为空|URL不能超过255个字符'],
        ['domain_name', 'max:60', '域名不能超过60个字符'],
        ['http_referer', 'max:255', '上一个页面URL不能超过250个字符'],
        ['http_method', 'require|max:10', '请求方式不能为空|请求方式不能超过10个字符'],
        ['add_time', 'require|number|gt:0', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' => ['ip', 'content', 'login_name', 'login_id', 'route', 'http_method', 'add_time'],
        'edit' => ['ip', 'content', 'login_name', 'login_id', 'route', 'http_method', 'add_time'],
        'del' => ['id'],
    ];
}