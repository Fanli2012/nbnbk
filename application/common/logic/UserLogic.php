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
            ['password', 'require|max:18', '密码不能为空|密码名不能超过18个字符']
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
		
		if(isset($data['from']) && $data['from']>=0)
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
     * @return array
     */
	public function wxLogin($data)
    {
		$user = $this->getModel()->getOne(array('openid'=>$data['openid']));
		if(!$user){return ReturnData::create(ReturnData::PARAMS_ERROR, null, '用户不存在');}
		
		//更新登录时间
		$this->getModel()->edit(['login_time'=>time()], ['id'=>$user['id']]);
		
		//获取用户信息
		$user_info = $this->getUserInfo(['id'=>$user['id']]);
		
		//生成Token
		$token = logic('Token')->getToken(Token::TOKEN_TYPE_WEIXIN, $user_info['id']);
		$user_info['token'] = $token;
		
		return ReturnData::create(ReturnData::SUCCESS, $user_info, '登录成功');
    }
    
    //注册
    public function wxRegister($data)
	{
        if(empty($data)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $data['add_time'] = time();
        
        $validator = $this->getValidate($data, 'wx_register');
        if ($validator->fails()){return ReturnData::create(ReturnData::PARAMS_ERROR, null, $validator->errors()->first());}
        
        $user_id = $this->getModel()->add($data);
        if(!$user_id){return ReturnData::create(ReturnData::SYSTEM_FAIL);}
        
        //生成token
		$token = logic('Token')->getToken(Token::TOKEN_TYPE_WEIXIN, $user_id);
		
        return ReturnData::create(ReturnData::SUCCESS, $token, '注册成功');
    }
}