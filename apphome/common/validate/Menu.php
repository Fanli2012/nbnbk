<?php
namespace app\common\validate;
use think\Validate;

class Menu extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['pid', 'number', '父级ID必须是数字'],
        ['module', 'require|alphaDash|max:50','模型必填|模型格式不正确|模型不能超过50个字符'],
        ['controller', 'require|alphaDash|max:50','控制器必填|控制器格式不正确|控制器不能超过50个字符'],
        ['action', 'require|alphaDash|max:50','方法必填|方法格式不正确|方法不能超过50个字符'],
        ['data', 'max:50','额外参数不能超过50个字符'],
        ['type', 'in:0,1','菜单类型，1：权限认证+菜单；0：只作为菜单'],
        ['status', 'in:0,1','状态，1显示，0不显示'],
        ['name', 'require|max:50','名称必填|名称不能超过50个字符'],
        ['icon', 'require|alphaDash|max:50','菜单图标必填|菜单图标格式不正确|菜单图标不能超过50个字符'],
        ['des', 'max:250','备注不能超过250个字符'],
        ['listorder', 'number','排序必须是数字'],
    ];
    
    protected $scene = [
        'add'  => ['pid', 'module', 'controller', 'action', 'data', 'type', 'status', 'name', 'icon', 'des', 'listorder'],
        'add'  => ['pid', 'module', 'controller', 'action', 'data', 'type', 'status', 'name', 'icon', 'des', 'listorder'],
        'del'  => ['id'],
    ];
}