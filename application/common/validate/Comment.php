<?php

namespace app\common\validate;

use think\Validate;
use app\common\lib\Helper;
use app\common\lib\Validator;

class Comment extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number|max:11', 'ID不能为空|ID必须是数字|ID格式不正确'],
        ['parent_id', 'number|max:11', '父级ID必须是数字|父级ID格式不正确'],
        ['user_id', 'require|number|max:11', '用户ID不能为空|用户ID必须是数字|用户ID格式不正确'],
        ['comment_type', 'require|in:0,1,2,3', '用户评论的类型不能为空|用户评论的类型:0评论的是商品,1评论的是文章'],
        ['id_value', 'require|number|max:11', '文章或者商品的ID不能为空|文章或者商品的ID必须是数字|文章或者商品的ID格式不正确'],
        ['comment_rank', 'in:0,1,2,3,4,5', '1到5星'],
        ['content', 'require|max:240', '评论内容不能为空|评论内容不能超过240个字符'],
        ['is_anonymous', 'in:0,1', '是否匿名，0否'],
        ['status', 'in:0,1', '是否被管理员批准显示;1是;0未批准显示'],
        ['ip_address', 'max:20', '评论时的用户IP格式不正确'],
        ['add_time', 'require|number|max:11', '添加时间不能为空|添加时间格式不正确|添加时间格式不正确'],
    ];

    protected $scene = [
        'add' =>  ['parent_id', 'user_id', 'comment_type', 'id_value', 'comment_rank', 'content' => 'max:240', 'is_anonymous', 'ip_address'],
        'edit' => ['parent_id', 'user_id', 'comment_type', 'id_value', 'comment_rank', 'content' => 'max:240', 'is_anonymous', 'ip_address'],
        'del' =>  ['id', 'user_id'],
		'add_common_comment' => ['parent_id', 'user_id', 'comment_type', 'id_value', 'content', 'is_anonymous', 'status', 'ip_address', 'add_time'],
    ];
}