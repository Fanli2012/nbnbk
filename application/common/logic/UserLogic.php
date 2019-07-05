<?php
namespace app\common\logic;
use think\Loader;
use think\Validate;
use app\common\lib\ReturnData;
use app\common\model\User;
use app\common\model\Token;

class UserLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getModel()
    {
        return new User();
    }
    
    public function getValidate()
    {
        return Loader::validate('User');
    }
    
    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);
        
        if($res['list'])
        {
            foreach($res['list'] as $k=>$v)
            {
                //$res['list'][$k] = $this->getDataView($v);
				$res['list'][$k] = $res['list'][$k]->append(array('status_text','sex_text'))->toArray();
            }
        }
        
        return $res;
    }
    
    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);
        
        $res = $res->each(function($item, $key){
            //$item = $this->getDataView($item);
            return $item;
        });
        
        return $res;
    }
    
    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);
        
        /* if($res)
        {
            foreach($res as $k=>$v)
            {
                //$res[$k] = $this->getDataView($v);
            }
        } */
        
        return $res;
    }
    
    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if(!$res){return false;}
        
        //$res = $this->getDataView($res);
		$res = $res->append(array('status_text','sex_text'))->toArray();
        
        return $res;
    }
    
    //添加
    public function add($data = array(), $type=0)
    {
        if(empty($data)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('add')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $res = $this->getModel()->add($data,$type);
        if(!$res){return ReturnData::create(ReturnData::FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $res);
    }
    
    //修改
    public function edit($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $res = $this->getModel()->edit($data,$where);
        if(!$res){return ReturnData::create(ReturnData::FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $res);
    }
    
    //删除
    public function del($where)
    {
        if(empty($where)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('del')->check($where);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $res = $this->getModel()->del($where);
        if(!$res){return ReturnData::create(ReturnData::FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $res);
    }
    
    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = array())
    {
        return getDataAttr($this->getModel(),$data);
    }
	
    //获取用户详情
    public function getUserInfo($where)
    {
        $user = $this->getModel()->getOne($where);
        if(!$user){return false;}
        
		if($user['pay_password']){$user['pay_password'] = 1;}else{$user['pay_password'] = 0;}
		unset($user['password']);
        
        $user['reciever_address'] = model('UserAddress')->getOne(array('id'=>$user['address_id']));
        $user['collect_goods_count'] = model('UserGoodsCollect')->getCount(array('user_id'=>$user['id']));
        $user['bonus_count'] = model('UserBonus')->getCount(array('user_id'=>$user['id'],'status'=>0));
        
        return $user;
    }
    
	/**
     * 用户名/手机号/邮箱+密码登录
     * @param string $data['user_name'] 用户名
     * @param string $data['password'] 密码
     * @param string $data['from'] 来源：0app,1admin,2weixin,3wap,4pc,5miniprogram
     * @return array
     */
	public function login($data)
    {
		//验证数据
        $validate = new Validate([
            ['user_name', 'require|max:30', '账号不能为空|账号不能超过30个字符'],
            ['password', 'require|length:6,18', '密码不能为空|密码6-18位']
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }
		
		$user_name = $data['user_name'];
		$password = $this->passwordEncrypt($data['password']);
		//用户名/手机号/邮箱+密码
		$user = $this->getModel()->getDb()->where(function($query) use ($user_name,$password){$query->where('user_name',$user_name)->where('password',$password)->where('delete_time',User::USER_UNDELETE);})->whereOr(function($query) use ($user_name,$password){$query->where('email',$user_name)->where('password',$password)->where('delete_time',User::USER_UNDELETE);})->whereOr(function($query) use ($user_name,$password){$query->where('mobile',$user_name)->where('password',$password)->where('delete_time',User::USER_UNDELETE);})->find();
        if(!$user){ return ReturnData::create(ReturnData::PARAMS_ERROR, null, '登录名或密码错误'); }
        
		//更新登录时间
		$this->getModel()->edit(['login_time'=>time()], ['id'=>$user['id']]);
		
		//获取用户信息
		$user_info = $this->getUserInfo(['id'=>$user['id']]);
		
		if(isset($data['from']) && $data['from']!='')
		{
			//生成Token
			$token = logic('Token')->getToken($user_info['id'], $data['from']);
			if(!$token){return ReturnData::create(ReturnData::PARAMS_ERROR, null, 'Token生成失败');}
			$user_info['token'] = $token;
		}
		
		return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
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
	public function wxLogin($data)
    {
		$edit_user = array();
		$user = $this->getModel()->getOne(array('openid'=>$data['openid']));
		if(!$user)
		{
			$data['add_time'] = $data['update_time'] = time();
			
			//默认用户名
			if(!(isset($data['user_name']) && !empty($data['user_name'])))
			{
				$data['user_name'] = date('YmdHis').rand(1000,9999);
			}
			
			//默认密码123456
			/* if(!(isset($data['password']) && !empty($data['password'])))
			{
				$data['password'] = $this->passwordEncrypt('123456');
			} */
			
			$check = $this->getValidate()->scene('wx_register')->check($data);
			if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
			
			if (isset($data['parent_mobile']) && $data['parent_mobile'] != '')
			{
				$parent_user = $this->getModel()->getOne(array('mobile'=>$data['parent_mobile']));
				if(!$parent_user)
				{
					return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在或推荐人手机号错误');
				}
				
				$data['parent_id'] = $parent_user['id'];
			}
			
			//判断用户名
			if (isset($data['user_name']) && !empty($data['user_name']))
			{
				if ($this->getModel()->getOne(array('user_name'=>$data['user_name'])))
				{
					return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户名已存在');
				}
			}
			
			$user_id = $this->getModel()->add($data);
			if(!$user_id){return ReturnData::create(ReturnData::SYSTEM_FAIL);}
			
			//更新用户名user_name，微信登录没有用户名
			$edit_user['user_name'] = 'u'.$user_id;
			$user['id'] = $user_id;
		}
		
		//更新登录时间
		$edit_user['login_time'] = time();
		$this->getModel()->edit($edit_user, array('id'=>$user['id']));
		
		//获取用户信息
		$user_info = $this->getUserInfo(['id'=>$user['id']]);
		
		//生成Token
		$token = logic('Token')->getToken($user_info['id'], Token::TOKEN_TYPE_WEIXIN);
		$user_info['token'] = $token;
		
		return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
    }
    
	/**
     * 用户名+密码注册
	 * @param string $data['user_name'] 用户名
	 * @param string $data['mobile'] 手机号
	 * @param string $data['password'] 密码
	 * @param int $data['parent_id'] 推荐人ID
	 * @param string $data['parent_mobile'] 推荐人手机号
     * @return array
     */
    public function register($data)
	{
        if(empty($data)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $data['add_time'] = $data['update_time'] = time();
        
		$check = $this->getValidate()->scene('register')->check($data);
		if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
		
        if (isset($data['parent_mobile']) && $data['parent_mobile'] != '')
		{
            if($user = $this->getModel()->getOne(array('mobile'=>$data['parent_mobile'])))
            {
                $data['parent_id'] = $user['id'];
            }
            else
            {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在或推荐人手机号错误');
            }
        }
        
        if (isset($data['user_name']) && $data['user_name'] != '')
		{
            if ($this->getModel()->getOne(array('user_name'=>$data['user_name'])))
            {
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'用户名已存在');
            }
        }
		
        $data['password'] = $this->passwordEncrypt($data['password']);
		
        $user_id = $this->getModel()->add($data);
        if(!$user_id){return ReturnData::create(ReturnData::SYSTEM_FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $user_id, '注册成功');
    }
	
    //用户信息修改
    public function userInfoUpdate($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
		//更新时间
        if(!(isset($data['update_time']) && !empty($data['update_time']))){$data['update_time'] = time();}
		
		//验证数据
        $validate = new Validate([
			['parent_id', 'number|max:11','推荐人ID必须是数字|推荐人ID格式不正确'],
			['email', 'email','邮箱格式不正确'],
			['nickname', 'max:30','昵称不能超过30个字符'],
			['user_name', 'max:30|regex:/^[-_a-zA-Z0-9]{3,18}$/i','用户名不能超过30个字符|用户名格式不正确'],
			['head_img', 'max:250','头像格式不正确'],
			['sex', 'in:0,1,2','性别：1男2女'],
			['birthday', 'regex:/\d{4}-\d{2}-\d{2}/','生日格式不正确'],
			['address_id', 'number|max:11','收货地址ID必须是数字|收货地址ID格式不正确'],
			['refund_account', 'max:30','退款账户不能超过30个字符'],
			['refund_name', 'max:20','退款姓名不能超过20个字符'],
			['signin_time', 'number|max:11', '签到时间格式不正确|签到时间格式不正确'],
			['group_id', 'number|max:11','分组ID必须是数字|分组ID格式不正确'],
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }
		
        $record = $this->getModel()->getOne($where);
        if(!$record){return ReturnData::create(ReturnData::RECORD_NOT_EXIST);}
        
        //判断用户名
        if(isset($data['user_name']) && $data['user_name'] != '')
        {
            $where_user_name['user_name'] = $data['user_name'];
			$where_user_name['id'] = ['<>',$record['id']]; //排除自身
            if($this->getModel()->getOne($where_user_name)){
                return ReturnData::create(ReturnData::FAIL, null, '该用户名已存在');
            }
        }
        
        //判断邮箱
        if(isset($data['email']) && $data['email'] != '')
        {
            $where_user_name['email'] = $data['email'];
			$where_user_name['id'] = ['<>',$record['id']]; //排除自身
            if($this->getModel()->getOne($where_user_name)){
                return ReturnData::create(ReturnData::FAIL, null, '该邮箱已存在');
            }
        }
        
        $res = $this->getModel()->edit($data,$where);
        if(!$res){return ReturnData::create(ReturnData::FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $res);
    }
    
    //修改用户密码
    public function userPasswordUpdate($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $check = $this->getValidate()->scene('user_password_update')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());}
        
        $user = $this->getModel()->getOne($where);
        if(!$user){return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');}
        
        if($this->passwordEncrypt($data['old_password']) != $user['password'])
		{
			return ReturnData::create(ReturnData::PARAMS_ERROR, null, '旧密码错误');
		}
        
		$data['password'] = $this->passwordEncrypt($data['password']);
        $res = $this->getModel()->edit($data, $where);
        if(!$res){return ReturnData::create(ReturnData::FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS,$res);
    }
    
    //修改用户支付密码
    public function userPayPasswordUpdate($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        $check = $this->getValidate()->scene('user_pay_password_update')->check($data);
        if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());}
        
        $user = $this->getModel()->getOne($where);
        if(!$user){return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');}
        
		if($user['pay_password'])
		{
			if($this->passwordEncrypt($data['old_pay_password']) != $user['pay_password'])
			{
				return ReturnData::create(ReturnData::PARAMS_ERROR, null, '旧支付密码错误');
			}
		}
        
		$data['pay_password'] = $this->passwordEncrypt($data['pay_password']);
        $res = $this->getModel()->edit($data, $where);
        if(!$res){return ReturnData::create(ReturnData::FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $res);
    }
    
    /**
     * 签到
	 * @param string $where['id'] 用户ID
     * @return array
     */
	public function signin($where)
    {
		$where['status'] = User::USER_STATUS_NORMAL;
        $user = $this->getModel()->getOne($where);
        if(!$user){return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');}
		
		$signin_time='';
		if(!empty($user['signin_time'])){$signin_time = date('Ymd', $user['signin_time']);} //签到时间
		
		$time = time();
		$today = date('Ymd', $time); //今日日期
		
		if($signin_time == $today){return ReturnData::create(ReturnData::FAIL, null, '今日已签到');}
		
		$signin_point = (int)sysconfig('CMS_SIGN_POINT'); //签到积分
		$res = logic('UserPoint')->add(array('type'=>0, 'point'=>$signin_point, 'desc'=>'签到', 'user_id'=>$user['id'])); //添加签到积分记录，并增加用户积分
		if($res['code'] != ReturnData::SUCCESS)
		{
			return ReturnData::create(ReturnData::FAIL, null, $res['msg']);
		}
		$this->getModel()->edit(array('signin_time' => $time), array('id'=>$user['id'])); //更新签到时间
		
		return ReturnData::create(ReturnData::SUCCESS, null, '签到成功');
    }
    
	//密码加密
	public function passwordEncrypt($password)
    {
        if($password == ''){return '';}
		return md5($password);
    }
}