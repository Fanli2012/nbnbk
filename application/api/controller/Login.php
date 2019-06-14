<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\UserLogic;
use app\common\model\User as UserModel;

class Login extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserLogic();
    }
    
	/**
     * 用户名/手机号/邮箱+密码登录
     * @param string $data['user_name'] 用户名
     * @param string $data['password'] 密码
     * @param int $data['from'] 来源：0app,1admin,2weixin,3wap,4pc,5miniprogram
     * @return array
     */
    public function index()
    {
		$user = $this->getLogic()->login(request()->param());
        exit(json_encode($user));
    }
    
	/**
     * 微信登录
     * @param string $data['openid'] 微信openid
	 * @param string $data['unionid'] 微信unionid
	 * @param int $data['sex'] 性别
	 * @param string $data['head_img'] 头像
	 * @param string $data['nickname'] 昵称
	 * @param int $data['parent_id'] 推荐人ID
	 * @param string $data['parent_mobile'] 推荐人手机号
     * @return array
     */
    public function wxLogin()
    {
		$user = $this->getLogic()->wxLogin(request()->param());
        exit(json_encode($user));
    }
    
    //用户名+密码注册
    public function register()
	{
		exit(json_encode($this->getLogic()->register(request()->param())));
    }
	
    //验证码登录
	public function verificationCodeLogin()
    {
        $mobile = input('mobile');
		$code = input('code', null);
        $type = input('type', null); //7表示验证码登录
		
        if (!$mobile || !$code)
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
		
		//判断验证码
        if ($type != VerifyCode::TYPE_LOGIN)
		{
            return ReturnData::create(ReturnData::INVALID_VERIFY_CODE);
        }
		
        $verifyCode = VerifyCode::isVerify($mobile, $code, $type);
        if (!$verifyCode)
		{
            return ReturnData::create(ReturnData::INVALID_VERIFY_CODE);
        }
        
        if ($user = MallDataManager::userFirst(['mobile'=>$mobile]))
		{
			//获取token
			$expired_at = Carbon::now()->addDay()->toDateTimeString();
			$token = Token::generate(Token::TYPE_SHOP, $user->id);
			
			$response = ReturnData::success();
			$response['data']=[
				'id' => $user->id, 'name' => $user->name, 'nickname' => $user->nickname, 'headimg' => (string)$user->head_img, 'token' => $token, 'expired_at' => $expired_at, 'mobile' => $user->mobile, 'hx_name' => 'cuobian'.$user->id, 'hx_pwd' => md5('cuobian'.$user->id)
			];
			
			return response($response);
        }
		else
		{
            return ReturnData::create(ReturnData::USER_NOT_EXIST);
        }
    }
}