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
     * @return array
     */
    public function wxLogin()
    {
		$user = $this->getLogic()->wxLogin(request()->param());
        exit(json_encode($user));
    }
    
    //注册
    public function wxRegister(Request $request)
	{
        $data['mobile'] = input('mobile','');
        $data['user_name'] = input('user_name','');
        $data['password'] = input('password','');
        $data['parent_id'] = 0;if(input('parent_id',null)!=null){$data['parent_id'] = input('parent_id');}
        $parent_mobile = input('parent_mobile','');
        
        if (($data['mobile']=='' && $data['user_name']=='') || $data['password']=='')
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        
        if ($parent_mobile!='')
		{
            if($user = model('User')->getOne(array('mobile'=>$parent_mobile)))
            {
                $data['parent_id'] = $user->id;
            }
            else
            {
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'推荐人不存在或推荐人手机号错误');
            }
        }
        
        if ($data['mobile']!='')
		{
            //判断手机格式
            if(!Helper::isValidMobile($data['mobile'])){return ReturnData::create(ReturnData::MOBILE_FORMAT_FAIL);}
            
            //判断是否已经注册
            if (model('User')->getOne(array('mobile'=>$data['mobile'])))
            {
                return ReturnData::create(ReturnData::MOBILE_EXIST);
            }
        }
		
        if ($data['user_name']!='')
		{
            if (model('User')->getOne(array('user_name'=>$data['user_name'])))
            {
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'用户名已存在');
            }
        }
        
        return $this->getLogic()->wxRegister($data);
    }
	
    //微信授权注册
    public function wxOauthRegister()
	{
        $data['openid'] = input('openid','');
        $data['unionid'] = input('unionid','');
        $data['sex'] = input('sex','');
        $data['head_img'] = input('head_img','');
        $data['nickname'] = input('nickname','');
        $data['parent_id'] = 0;if(input('parent_id',null)!=null){$data['parent_id'] = input('parent_id');}
        $data['user_name'] = date('YmdHis').dechex(date('His').rand(1000,9999));
        $data['password'] = md5('123456');
        
        if ($data['openid']=='')
		{
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }
        
		if (!model('User')->getOne(array('openid'=>$data['openid'])))
        {
            //添加用户
            $res = $this->getLogic()->wxRegister($data);
            if($res['code'] != ReturnData::SUCCESS){return $res;}
            
            //更新用户名user_name，微信登录没有用户名
            model('User')->edit(array('user_name'=>date('Ymd').'u'.$res['data']['uid']),array('id'=>$res['data']['uid']));
        }
        
        return $this->getLogic()->wxLogin(array('openid'=>$data['openid']));
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