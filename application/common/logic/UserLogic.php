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
		$password = md5($data['password']);
		//用户名/手机号/邮箱+密码
		$user = $this->getModel()->getDb()->where(function($query) use ($user_name,$password){$query->where('user_name',$user_name)->where('password',$password)->where('delete_time',User::USER_UNDELETE);})->whereOr(function($query) use ($user_name,$password){$query->where('email',$user_name)->where('password',$password)->where('delete_time',User::USER_UNDELETE);})->whereOr(function($query) use ($user_name,$password){$query->where('mobile',$user_name)->where('password',$password)->where('delete_time',User::USER_UNDELETE);})->find();
        if(!$user){return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在或者账号密码错误');}
        
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
				$data['user_name'] = date('YmdHis').dechex(date('His').rand(1000,9999));
			}
			
			//默认密码123456
			/* if(!(isset($data['password']) && !empty($data['password'])))
			{
				$data['password'] = md5('123456');
			} */
			
			$check = $this->getValidate()->scene('wx_register')->check($data);
			if(!$check){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
			
			if (isset($data['parent_mobile']) && $data['parent_mobile'] != '')
			{
				if($parent_user = $this->getModel()->getOne(array('mobile'=>$data['parent_mobile'])))
				{
					$data['parent_id'] = $parent_user['id'];
				}
				else
				{
					return ReturnData::create(ReturnData::PARAMS_ERROR, null, '推荐人不存在或推荐人手机号错误');
				}
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
		$token = logic('Token')->getToken(Token::TOKEN_TYPE_WEIXIN, $user_info['id']);
		$user_info['token'] = $token;
		
		return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
    }
    
    //用户名+密码注册
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
		
        $data['password'] = md5($data['password']);
		
        $user_id = $this->getModel()->add($data);
        if(!$user_id){return ReturnData::create(ReturnData::SYSTEM_FAIL);}
        
        return ReturnData::create(ReturnData::SUCCESS, $user_id, '注册成功');
    }
	
}