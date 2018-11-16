<?php
namespace app\common\logic;
use think\Loader;
use think\Validate;
use app\common\lib\ReturnData;
use app\common\model\Admin;

class AdminLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }
    
    public function getModel()
    {
        return new Admin();
    }
    
    public function getValidate()
    {
        return Loader::validate('Admin');
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
        if($check === false){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        if(!isset($data['add_time'])){$data['add_time']=$data['update_time']=time();}
        
        //判断手机号码
        if(isset($data['mobile']) && !empty($data['mobile']))
        {
            if($this->getModel()->getOne(['mobile'=>$data['mobile']])){
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'手机号码已经存在');
            }
        }
        
        //判断邮箱
        if(isset($data['email']) && !empty($data['email']))
        {
            if($this->getModel()->getOne(['email'=>$data['email']])){
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'邮箱已经存在');
            }
        }
        
        $res = $this->getModel()->add($data,$type);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //修改
    public function edit($data, $where = array())
    {
        if(empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        if(!isset($data['update_time'])){$data['update_time']=time();}
        
        $admin = $this->getModel()->getOne($where);
        if(!$admin){return ReturnData::create(ReturnData::FAIL,null,'记录不存在');}
        
        //判断手机号码
        if(isset($data['mobile']) && !empty($data['mobile']))
        {
            $where2['mobile'] = $data['mobile'];
            $where2['id'] = ['<>',$admin['id']]; //排除自身
            if($this->getModel()->getOne($where2)){
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'手机号码已经存在');
            }
        }
        
        //判断邮箱
        if(isset($data['email']) && !empty($data['email']))
        {
            $where3['email'] = $data['email'];
            $where3['id'] = ['<>',$admin['id']]; //排除自身
            if($this->getModel()->getOne($where3)){
                return ReturnData::create(ReturnData::PARAMS_ERROR,null,'邮箱已经存在');
            }
        }
        
        $res = $this->getModel()->edit($data,$where);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    //删除
    public function del($where)
    {
        if(empty($where)){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        
        $check = $this->getValidate()->scene('del')->check($where);
        if($check === false){return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->getValidate()->getError());}
        
        $res = $this->getModel()->del($where);
        if($res){return ReturnData::create(ReturnData::SUCCESS,$res);}
        
        return ReturnData::create(ReturnData::FAIL);
    }
    
    /**
     * 登录
     * @param string $data['name'] 用户名
     * @param string $data['pwd'] 密码
     * @return array
     */
    public function login($data)
    {
        //验证数据
        $validate = new Validate([
            ['name', 'require|max:30', '用户名不能为空|用户名不能超过30个字符'],
            ['pwd', 'require|max:30', '密码不能为空|密码格式不正确']
        ]);
        if (!$validate->check($data)) {
            return ReturnData::create(ReturnData::FAIL, null, $validate->getError());
        }
        
        $name = $data['name'];
        $pwd = md5($data['pwd']);
        
        //用户名/邮箱/手机登录
        $admin = $this->getModel()->where(function($query) use ($name,$pwd){$query->where('name',$name)->where('pwd',$pwd);})->whereOr(function($query) use ($name,$pwd){$query->where('email',$name)->where('pwd',$pwd);})->whereOr(function($query) use ($name,$pwd){$query->where('mobile',$name)->where('pwd',$pwd);})->find();
        if($admin)
        {
            $admin = $admin->append(['role_name','status_text'])->toArray();
			//$admin['role_name'] = model('AdminRole')->getValue(['id'=>$admin['role_id']], 'name');
			//更新登录时间
			$this->getModel()->edit(['login_time'=>time()], ['id'=>$admin['id']]);
			
            return ReturnData::create(ReturnData::SUCCESS, $admin);
        }
        
        return ReturnData::create(ReturnData::FAIL);
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
}