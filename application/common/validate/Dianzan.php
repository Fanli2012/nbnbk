<?php

namespace app\common\validate;

use think\Validate;

class Dianzan extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['id_value', 'require|number|max:11|gt:0', '评论或文章的ID不能为空|评论或文章的ID必须是数字|评论或文章的ID格式不正确|评论或文章的ID格式不正确'],
        ['type', 'in:0,1,2,3,4,5', '用户点赞类型，0评论，1文章'],
        ['user_id', 'require|number|max:11|gt:0', '用户ID不能为空|用户ID必须是数字|用户ID格式不正确|用户ID格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add'  => ['id_value', 'type', 'user_id'],
        'edit' => ['id_value', 'type', 'user_id'],
        'del'  => ['id'],
    ];
}