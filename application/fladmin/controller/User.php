<?php
namespace app\fladmin\controller;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
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
    
    //列表
    public function index()
    {
        $where = array();
        if(!empty($_REQUEST['keyword']))
        {
            $where['nickname'] = array('like', '%'.$_REQUEST['keyword'].'%');
        }
        $list = $this->getLogic()->getPaginate($where, array('id'=>'desc'));
		
		$this->assign('page',$list->render());
        $this->assign('list',$list);
		//echo '<pre>';print_r($list);exit;
		return $this->fetch();
    }
	
    //添加
	public function add()
    {
        if(Helper::isPostRequest())
        {
            $res = $this->getLogic()->add($_POST);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'), '', 1);
            }
            
            $this->error($res['msg']);
        }
        
        return $this->fetch();
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);
            
            $res = $this->getLogic()->edit($_POST,$where);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'), '', 1);
            }
            
            $this->error($res['msg']);
        }
        
        if(!checkIsNumber(input('id',null))){$this->error('参数错误');}
        $where['id'] = input('id');
        $this->assign('id', $where['id']);
        
        $post = $this->getLogic()->getOne($where);
        $this->assign('post', $post);
        
        return $this->fetch();
    }
	
    //删除
    public function del()
    {
        if(!checkIsNumber(input('id',null))){$this->error('删除失败！请重新提交');}
        $where['id'] = input('id');
        
        $res = $this->getLogic()->del($where);
		if($res['code'] == ReturnData::SUCCESS)
        {
            $this->success('删除成功');
        }
		
        $this->error($res['msg']);
    }
	
    //会员账户记录
    public function money()
    {
        $where = '';
        if(isset($_REQUEST["user_id"]))
        {
            $where['user_id'] = $_REQUEST["user_id"];
        }
        
        $posts = parent::pageList('user_money',$where);
		
        if($posts)
        {
            foreach($posts as $k=>$v)
            {
                $posts[$k]->user = DB::table('user')->where('id', $v->user_id)->first();
            }
        }
        
        $data['posts'] = $posts;
        return view('admin.user.money', $data);
    }
    
    //人工充值
    public function manualRecharge()
    {
        if(Helper::isPostRequest())
        {
            if(!is_numeric($_POST["money"]) || $_POST["money"]==0){error_jump('金额格式不正确');}
            
            unset($_POST["_token"]);
            
            if($_POST["money"]>0)
            {
                DB::table('user')->where(['id'=>$_POST["id"]])->increment('money', $_POST["money"]);
                $user_money['type'] = 0;
            }
            else
            {
                DB::table('user')->where(['id'=>$_POST["id"]])->decrement('money', abs($_POST["money"]));
                $user_money['type'] = 1;
            }
            
            $user_money['user_id'] = $_POST["id"];
            $user_money['add_time'] = time();
            $user_money['money'] = abs($_POST["money"]);
            $user_money['des'] = '后台充值';
            $user_money['user_money'] = DB::table('user')->where(array('id'=>$_POST["id"]))->value('money');
            
            //添加用户余额记录
            DB::table('user_money')->insert($user_money);
            
            success_jump('操作成功', route('admin_user'));
        }
        
        $data['user'] = object_to_array(DB::table('user')->select('user_name', 'mobile', 'money', 'id')->where('id', $_REQUEST["user_id"])->first(), 1);
        if(!$data['user']){error_jump('参数错误');}
        
        return view('admin.user.manualRecharge', $data);
    }
    
}