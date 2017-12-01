<?php
namespace app\common\model;

use think\Db;

class User extends Base
{
    protected $hidden = array('password','pay_password');
    
    //获取列表
	public static function getList(array $param)
    {
        extract($param); //参数：limit，offset
        
        $where = '';
        $limit  = isset($limit) ? $limit : 10;
        $offset = isset($offset) ? $offset : 0;
        
        $model = new self;
        
        if(isset($group_id)){$where['group_id'] = $group_id;}
        
        if($where != '')
        {
            $model = $model->where($where);
        }
        
        $res['count'] = $model->count();
        $res['list'] = array();
        
		if($res['count']>0)
        {
            $res['list']  = $model->field(['id','user_name','email','sex','money','point','mobile','nickname','add_time'])->limit($offset,$limit)->order('id desc')->select();
        }
        else
        {
            return false;
        }
        
        return $res;
    }
    
    //获取一条用户信息
	public static function getOne($id)
    {
        $user = self::where(array('id'=>$id))->find();
        if(!$user){return false;}
        $user['reciever_address'] = UserAddress::getOne($user['address_id']);
        
		return $user;
    }
    
    public static function add(array $data)
    {
        $id = $this->allowField(true)->isUpdate(false)->save($data);
        
        return $id;
    }
    
    public static function modify($where, array $data)
    {
        return $this->allowField(true)->isUpdate(true)->save($data, $where);
    }
    
    //删除一条记录
    public static function remove($where)
    {
        return $this->where($where)->delete();
    }
    
    //获取一条用户信息
	public static function getOneUser($where)
    {
        $user = self::where($where)->find();
        if(!$user){return false;}
        
		return $user;
    }
    
    //获取用户信息
    public static function getUserInfo($user_id)
    {
        $user = self::where('id', $user_id)->find();
        if(!$user){return false;}
        $user['reciever_address'] = UserAddress::getOne($user['address_id']);
        $user['collect_goods_count'] = CollectGoods::where('user_id', $user_id)->count();
        $user['bonus_count'] = UserBonus::where(array('user_id'=>$user_id,'status'=>0))->count();
        
        if($user['pay_password']){$user['pay_password'] = 1;}else{$user['pay_password'] = 0;}
        
        return $user;
    }

    //修改用户密码、支付密码
    public static function userPasswordUpdate($where,array $param)
    {
        extract($param);
        $data = '';

        $user = self::where($where)->find();
        if(!$user){return false;}
        
        if(isset($old_password) && $old_password!=$user['password']){return false;} //旧密码错误
        if(isset($password) && $password==''){return false;} //新密码为空

        if(isset($old_pay_password) && $old_pay_password!=$user['pay_password']){return false;}
        if(isset($pay_password) && $pay_password==''){return false;}

        if(isset($password)){$data['password'] = $password;}
        if(isset($pay_password)){$data['pay_password'] = $pay_password;}

        if ($data != '' && $this->allowField(true)->isUpdate(true)->save($data, $where))
        {
            return true;
        }

        return false;
    }
    
    //注册
    public static function wxRegister(array $param)
	{
        extract($param); //参数
        
        if(isset($user_name)){$data['user_name'] = $user_name;}
        if(isset($mobile)){$data['mobile'] = $mobile;}
        if(isset($password)){$data['password'] = $password;} //md5加密
        if(isset($parent_id) && !empty($parent_id)){$data['parent_id'] = $parent_id;}
        if(isset($openid)){$data['openid'] = $openid;}
        if(isset($sex)){$data['sex'] = $sex;}
        if(isset($head_img)){$data['head_img'] = $head_img;}
        if(isset($nickname)){$data['nickname'] = $nickname;}
        
        if (isset($data) && $id = $this->allowField(true)->isUpdate(false)->save($data))
        {
            //生成token
			return Token::getToken(Token::TYPE_WEIXIN, $id);
        }
        
        return false;
    }
    
    //用户登录
	public static function wxLogin(array $param)
    {
        extract($param); //参数
        
        if(isset($openid) && !empty($openid))
        {
            $user = self::where(array('openid'=>$openid))->find();
        }
        else
        {
            $user = $this->whereOr(array('mobile'=>$user_name,'password'=>$password),array('user_name'=>$user_name,'password'=>$password))->find();
        }
        
        if(!isset($user)){return false;}
        
        $res = self::getUserInfo($user['id']);
        $token = Token::getToken(Token::TYPE_WEIXIN, $user['id']);
        
        foreach($token as $k=>$v)
        {
            $res->$k = $v;
        }
        
		return $res;
    }
}