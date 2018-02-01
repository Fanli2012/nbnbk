<?php
namespace app\common\validate;

use think\Validate;

class Article extends Validate
{
    // 验证规则
    protected $rule = [
        ['typeid', 'require|number','栏目ID必填|栏目ID必须是数字'],
        ['title', 'require|max:150','标题必填|标题不能超过150个字符'],
        ['ischeck', 'in:0,1','审核状态：0审核，1未审核'],
        ['click', 'number', '点击量必须是数字'],
        ['addtime', 'number', '添加时间必须是数字'],
    ];
    
    protected $scene = [
        'add' => ['typeid', 'title', 'addtime'],
    ];
}