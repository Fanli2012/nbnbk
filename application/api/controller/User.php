<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\UserLogic;
use app\common\model\User as UserModel;

class User extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserLogic();
    }
    
    //我的分销团队
    public function myteam()
	{
        //参数
        $limit = input('limit/d', 10);
        $offset = input('offset/d', 0);
        $where = array();
		$where['parent_id'] = $this->login_info['id'];
        if(input('sex', '') !== ''){$where['sex'] = input('sex');}
        if(input('group_id', '') !== ''){$where['group_id'] = input('group_id');}
		if(input('status', '') === ''){$where['status'] = UserModel::USER_STATUS_NORMAL;}else{if(input('status') != -1){$where['status'] = input('status');}}
        $orderby = input('orderby','id desc');
        if($orderby=='rand()'){$orderby = array('orderRaw','rand()');}
        
        $res = $this->getLogic()->getList($where,$orderby,'parent_id,mobile,nickname,user_name,head_img,sex,commission,consumption_money,user_rank,status,add_time',$offset,$limit);
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if(!empty($v['head_img'])){$res['list'][$k]['head_img'] = (substr($v['head_img'], 0, strlen('http')) === 'http') ? $v['head_img'] : sysconfig('CMS_SITE_CDN_ADDRESS').$v['head_img'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        $where['id'] = $this->login_info['id'];
        
		$res = $this->getLogic()->getUserInfo($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::RECORD_NOT_EXIST)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            unset($_POST['id']);
			$where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }
    
    //用户信息修改，仅能修改一些不敏感的信息
    public function user_info_update()
    {
        if(Helper::isPostRequest())
        {
            $where['id'] = $this->login_info['id'];
            
            $data = array();
            if(input('user_name', '')!==''){$data['user_name'] = input('user_name');}
            if(input('email', '')!==''){$data['email'] = input('email');}
            if(input('sex', '')!==''){$data['sex'] = input('sex');}
            if(input('birthday', '')!==''){$data['birthday'] = input('birthday');}
            if(input('address_id', '')!==''){$data['address_id'] = input('address_id');}
            if(input('nickname', '')!==''){$data['nickname'] = input('nickname');}
            if(input('group_id', '')!==''){$data['group_id'] = input('group_id');}
            if(input('head_img', '')!==''){$data['head_img'] = input('head_img');}
            if(input('refund_account', '')!==''){$data['refund_account'] = input('refund_account');}
            if(input('refund_name', '')!==''){$data['refund_name'] = input('refund_name');}
            
			$res = $this->getLogic()->userInfoUpdate($data, $where);
            exit(json_encode($res));
        }
    }
    
    //修改用户密码
    public function user_password_update()
    {
        $data['password'] = input('password', '');
		$data['old_password'] = input('old_password', '');
		if($data['password'] == $data['old_password']){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, '新旧密码相同')));}
        
        $res = $this->getLogic()->userPasswordUpdate($data, array('id' => $this->login_info['id']));
		exit(json_encode($res));
    }
    
    //修改用户支付密码
    public function user_pay_password_update()
    {
        $data['pay_password'] = input('pay_password', '');
		$data['old_pay_password'] = input('old_pay_password', '');
		
		if($data['pay_password'] == $data['old_pay_password']){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR, null, '新旧支付密码相同')));}
        
        $res = $this->getLogic()->userPayPasswordUpdate($data, array('id' => $this->login_info['id']));
		exit(json_encode($res));
    }
    
    //签到
	public function signin()
	{
		$res = $this->getLogic()->signin(array('id'=>$this->login_info['id']));
		exit(json_encode($res));
    }
    
    //修改密码
    public function change_password()
    {
        $mobile = input('mobile', null);
        $password = input('password', null); //新密码
		$oldPassword = input('oldPassword', null); //旧密码
		
		if (!$mobile || !$password || !$oldPassword)
		{
            return ReturnCode::create(ReturnCode::PARAMS_ERROR);
        }
		
		if($password == $oldPassword)
		{
			return ReturnCode::create(ReturnCode::PARAMS_ERROR,'新旧密码相同');
		}
		
		if (!Helper::isValidMobile($mobile))
		{
			return ReturnCode::create(ReturnCode::MOBILE_FORMAT_FAIL);
		}
		
		$user = MallDataManager::userFirst(['mobile'=>$mobile,'password'=>$oldPassword,'id'=>$this->login_info['id']]);
		
		if(!$user)
		{
			return ReturnCode::create(ReturnCode::PARAMS_ERROR,'手机或密码错误');
		}
		
		DB::table('user')->where(['mobile'=>$mobile,'password'=>$oldPassword,'id'=>$this->login_info['id']])->update(['password'=>$password]);
		
		MallDataManager::tokenDelete(['uid'=>$this->login_info['id']]);
		
		return ReturnCode::create(ReturnCode::SUCCESS);
    }
	
	//找回密码，不用输入旧密码
    public function find_password()
    {
        $mobile = input('mobile', null);
        $password = input('password', null);
		
        if ($mobile && $password)
		{
            if (!Helper::isValidMobile($mobile))
			{
                return response(ReturnCode::create(ReturnCode::MOBILE_FORMAT_FAIL));
            }
			
            //判断验证码是否有效
            $code = input('code', '');
            $type = input('type', null);
            if($type != VerifyCode::TYPE_CHANGE_PASSWORD)
                return response(ReturnCode::create(ReturnCode::INVALID_VERIFY_CODE,'验证码类型错误'));
            $verifyCode = VerifyCode::isVerify($mobile, $code, $type);
			
            if($verifyCode)
            {
                try
				{
                    DB::beginTransaction();
                    $verifyCode->status = VerifyCode::STATUS_USE;
                    $verifyCode->save();
					
                    if ($user = MallDataManager::userFirst(['mobile'=>$mobile]))
					{
                        DB::table('user')->where(['mobile'=>$mobile])->update(['password'=>$password]);
                        
						MallDataManager::tokenDelete(['uid'=>$user->id]);
						
						$response = response(ReturnCode::create(ReturnCode::SUCCESS));
                    }
					else
					{
                        $response = response(ReturnCode::create(ReturnCode::PARAMS_ERROR));
                    }
					
					DB::commit();
					
                    return $response;
                }
				catch (Exception $e)
				{
                    DB::rollBack();
                    return response(ReturnCode::error($e->getCode(), $e->getMessage()));
                }
            }
            else
            {
                return response(ReturnCode::create(ReturnCode::INVALID_VERIFY_CODE));
            }
        }
		else
		{
            return response(ReturnCode::create(ReturnCode::PARAMS_ERROR));
        }
    }
	
	//修改手机号
    public function change_mobile()
    {
        $mobile = input('mobile', null); //新手机号码
        $verificationCode = input('verificationCode', null); //新手机验证码
		$oldMobile = input('oldMobile', null); //旧手机号码
		$oldVerificationCode = input('oldVerificationCode', null); //旧手机验证码
		$type = input('type', null); //验证码类型
		
		if (!$mobile || !$verificationCode || !$oldMobile || !$oldVerificationCode || !$type)
		{
            return ReturnCode::create(ReturnCode::PARAMS_ERROR);
        }
		
		if (!Helper::isValidMobile($mobile))
		{
			return ReturnCode::create(ReturnCode::MOBILE_FORMAT_FAIL);
		}
		
		if($mobile == $oldMobile)
		{
			return ReturnCode::create(ReturnCode::PARAMS_ERROR,'新旧手机号码相同');
		}
		
		if($type != VerifyCode::TYPE_CHANGE_MOBILE)
		{
			return ReturnCode::create(ReturnCode::INVALID_VERIFY_CODE,'验证码类型错误');
        }
		
		$verifyCode = VerifyCode::isVerify($oldMobile, $oldVerificationCode, $type);
		if(!$verifyCode)
		{
			return ReturnCode::create(ReturnCode::INVALID_VERIFY_CODE);
		}
		
		$verifyCode = null;
		$verifyCode = VerifyCode::isVerify($mobile, $verificationCode, $type);
		if(!$verifyCode)
		{
			return ReturnCode::create(ReturnCode::INVALID_VERIFY_CODE);
		}
		
		$user = MallDataManager::userFirst(['mobile'=>$oldMobile,'id'=>$this->login_info['id']]);
		
		if(!$user)
		{
			return ReturnCode::create(ReturnCode::PARAMS_ERROR,'旧手机号码错误');
		}
		
		DB::table('user')->where(['mobile'=>$oldMobile,'id'=>$this->login_info['id']])->update(['mobile'=>$mobile]);
		
		MallDataManager::tokenDelete(['uid'=>$this->login_info['id']]);
		
		return ReturnCode::create(ReturnCode::SUCCESS);
    }
}