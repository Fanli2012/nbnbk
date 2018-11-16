<?php
namespace app\common\validate;
use think\Validate;
use app\common\lib\Helper;

class EmailVerifyCode extends Validate
{
    // 验证规则
    protected $rule = [
        ['id', 'require|number','ID必填|ID必须是数字'],
        ['code', 'require|max:10','验证码必填|验证码不能超过10个字符'],
        ['type', 'require|in:0,1,2,3,4,5,6,7,8,9','类型必填|0通用，注册，1:手机绑定业务验证码，2:密码修改业务验证码'],
        ['email', 'require|max:20|checkEmail','郵箱必填|郵箱不能超過20個字符'],
        ['status', 'in:0,1','0:未使用 1:已使用'],
        ['result', 'max:500', '返回结果不能超过500个字符'],
        ['captcha', 'require|checkCaptcha','验证码必填'],
    ];
    
    protected $scene = [
        'add' => ['email', 'text', 'status', 'result'],
        'del' => ['id'],
        'get_verifycode_by_smtp' => ['email', 'type', 'captcha'],
    ];
    
    /**
     * 邮箱验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkPhone($value,$rule,$data,$field)
    {
        if(Helper::isValidMobile($value))
        {
            return true;
        }
        
        return '手机号码格式不正确';
    }
    
    /**
     * 手机号码验证
     * 参数依次为验证数据，验证规则，全部数据(数组)，字段名
     */
    protected function checkEmail($value,$rule,$data,$field)
    {
        if(Helper::isValidEmail($value))
        {
            return true;
        }
        
        return '郵箱格式不正確';
    }
    
    // 图形验证码验证
    protected function checkCaptcha($value)
    {
        if(!captcha_check($value))
        {
            return '圖形驗證碼錯誤';
        }
        
        return true;
    }
}