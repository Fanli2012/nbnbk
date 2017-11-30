<?php
namespace app\common\validate;

use think\Validate;

class Car extends Validate
{
    // 验证规则
    protected $rule = [
        ['seller_id', 'require|number','车行ID必填|车行ID必须是数字'],
        ['car_vin', 'require|max:60','VIN码必填|VIN码不能超过60个字符'],
        ['is_transfer', 'in:0,1','是否转转车格式不正确'],
        ['price_sale', 'require|regex:/^\d{0,13}(\.\d{0,2})?$/', '售价必填|售价只能带2位小数的数字'],
    ];
    
    protected $scene = [
        'edit' => ['seller_id', 'price_sale'],
    ];
}