<?php
namespace app\fladmin\controller;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\TagLogic;

class Tag extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new TagLogic();
    }
    
    //列表
    public function index()
    {
        $where = array();
        if(!empty($_REQUEST["keyword"]))
        {
            $where['name'] = array('like','%'.$_REQUEST['keyword'].'%');
        }
        $list = $this->getLogic()->getPaginate($where,['id'=>'desc']);
		
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
        
        //获取该标签下的文章id
        $posts = db('taglist')->field('article_id')->where("tag_id=".$where['id'])->select();
        $aidlist = "";
        if(!empty($posts))
        {
            foreach($posts as $row)
            {
                $aidlist=$aidlist.','.$row['article_id'];
            }
        }
        $this->assign('aidlist',ltrim($aidlist, ","));
        
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
            $this->success("删除成功");
        }
		
        $this->error($res['msg']);
    }
    
    public function doadd()
    {
		$tagarc="";
		if(!empty($_POST["tagarc"])){$tagarc = str_replace("，",",",$_POST["tagarc"]);if(!preg_match("/^\d*$/",str_replace(",","",$tagarc))){$tagarc="";}} //Tag文章列表
        
        $_POST['pubdate'] = time();//更新时间
        $_POST['click'] = rand(200,500);//点击
        unset($_POST["tagarc"]);
        
		if($insertId = db('tagindex')->insert($_POST))
        {
            if($tagarc!="")
            {
                $arr=explode(",",$tagarc);
                
                foreach($arr as $row)
                {
                    $data2['tid'] = $insertId;
                    $data2['aid'] = $row;
                    db("taglist")->insert($data2);
                }
            }
            $this->success('添加成功', CMS_ADMIN.'Tag' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'Tag/add' , 3);
		}
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        if(!empty($_POST["keywords"])){$_POST['keywords']=str_replace("，",",",$_POST["keywords"]);}else{$_POST['keywords']="";}//关键词
        $_POST['pubdate'] = time();//更新时间
        $tagarc="";
		if(!empty($_POST["tagarc"])){$tagarc = str_replace("，",",",$_POST["tagarc"]);if(!preg_match("/^\d*$/",str_replace(",","",$tagarc))){$tagarc="";}} //Tag文章列表
        unset($_POST["tagarc"]);
        
		if(db('tagindex')->where("id=$id")->update($_POST))
        {
            //获取该标签下的文章id
            $posts = db("taglist")->field('aid')->where("tid=$id")->select();
            $aidlist = "";
            if(!empty($posts))
            {
                foreach($posts as $row)
                {
                    $aidlist = $aidlist.','.$row['aid'];
                }
            }
            $aidlist = ltrim($aidlist, ",");
            
            if($tagarc!="" && $tagarc!=$aidlist)
            {
                db("taglist")->where("tid=$id")->delete();
                
                $arr=explode(",",$tagarc);
                    
                foreach($arr as $row)
                {
                    $data2['tid'] = $id;
                    $data2['aid'] = $row;
                    db("taglist")->insert($data2);
                }
            }
            elseif($tagarc=="")
            {
                db("taglist")->where("tid=$id")->delete();
            }
            
            $this->success('修改成功', CMS_ADMIN.'Tag' , 1);
        }
		else
		{
			$this->error('修改失败', CMS_ADMIN.'Tag/edit?id='.$_POST["id"] , 3);
		}
    }
}