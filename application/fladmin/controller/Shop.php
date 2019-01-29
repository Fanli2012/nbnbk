<?php
namespace app\fladmin\controller;
use think\Db;
use app\common\lib\ReturnData;
use app\common\logic\ShopLogic;
use app\common\lib\Helper;

class Shop extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
	
    public function getLogic()
    {
        return new ShopLogic();
    }
    
    public function index()
    {
		$where = array();
        if(!empty($_REQUEST["keyword"]))
        {
            $where['title'] = array('like','%'.$_REQUEST['keyword'].'%');
        }
        if(!empty($_REQUEST["typeid"]) && $_REQUEST["typeid"]!=0)
        {
            $where['typeid'] = $_REQUEST["typeid"];
        }
        if(!empty($_REQUEST["id"]))
        {
            $where['typeid'] = $_REQUEST["id"];
        }
        $where['delete_time'] = 0; //未删除
        if(!empty($_REQUEST["status"]))
        {
            $where['status'] = $_REQUEST["status"];
        }
        if(isset($_REQUEST["tuijian"]))
        {
            $where['tuijian'] = $_REQUEST["tuijian"];
        }
        
        $posts = $this->getLogic()->getPaginate($where,'id desc',['body'],15);
		
		$this->assign('page',$posts->render());
        $this->assign('posts',$posts);
		
		return $this->fetch();
    }
    
    public function add()
    {
        if(Helper::isPostRequest())
        {
            if($_POST['expire_time']){$_POST['expire_time'] = strtotime($_POST['expire_time']);}
            if($_POST['mobile']){$_POST['user_name'] = $_POST['mobile'];}
            $_POST['password'] = $_POST['pay_password'] = md5('123456');
            $_POST['click'] = rand(200,500);
            
            $res = $this->getLogic()->add($_POST);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'));
            }
            
            $this->error($res['msg']);
        }
        
        return $this->fetch();
    }
    
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);
            
            if($_POST['mobile']){$_POST['user_name'] = $_POST['mobile'];}
            if($_POST['expire_time']){$_POST['expire_time'] = strtotime($_POST['expire_time']);}
            $res = $this->getLogic()->edit($_POST,$where);
            if($res['code'] == ReturnData::SUCCESS)
            {
                $this->success($res['msg'], url('index'));
            }
            
            $this->error($res['msg']);
        }
        
        if(!empty($_GET["id"])){$id = $_GET["id"];}else {$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('id', $id);
        
        $post = $this->getLogic()->getOne("id=$id");
        if($post['expire_time']){$post['expire_time'] = date('Y-m-d', $post['expire_time']);}else{$post['expire_time'] = '';}
		$this->assign('post', $post);
        
        return $this->fetch();
    }
    
    public function tuijian()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        unset($_GET['id']);
        $where['id'] = $id;
        
        $res = model('Shop')->edit(['tuijian'=>$_GET['tuijian']], $where);
        if($res['code'] == ReturnData::SUCCESS)
        {
            $this->success('操作成功');
        }
        
        $this->error('操作失败');
    }
    
    //通过审核
    public function tongguo()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        unset($_GET['id']);
        $where['id'] = $id;
        
        $res = model('Shop')->edit(array('status'=>1),$where);
        if($res)
        {
            $this->success('操作成功');
        }
        
        $this->error('操作失败');
    }
	
    public function del()
    {
		if(!empty($_GET["id"])){$id = $_GET["id"];}else{$this->error('参数错误', url('index'), '', 3);}
		
        unset($_GET['id']);
        $where['id'] = $id;
        
        $res = $this->getLogic()->del($where);
        if($res['code'] == ReturnData::SUCCESS)
        {
            $this->success($res['msg']);
        }
        
        $this->error($res['msg']);
    }
}