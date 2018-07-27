<?php
namespace app\shop\controller;
use think\Db;
use app\common\lib\ReturnData;
use app\common\logic\ArticleLogic;

class Article extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
	
    public function getLogic()
    {
        return new ArticleLogic();
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
        $where['shop_id'] = $this->login_info['id'];
        $list = $this->getLogic()->getPaginate($where,['tuijian'=>'desc','updated_at'=>'desc'],['body'],15);
		
		$this->assign('page',$list->render());
        $this->assign('list',$list);
		//echo '<pre>';var_dump($list);exit;
		return $this->fetch();
    }
    
    public function add()
    {
        $where['shop_id'] = $this->login_info['id'];
        
        $count = model('Arctype')->getCount($where);
        if($count>0){}else{$this->error('请先添加分类', url('shop/Arctype/add'));}
        
        $type_list = model('Arctype')->getAll($where,['listorder'=>'asc'],['content'],15);
        $this->assign('type_list',$type_list);
        
        return $this->fetch();
    }
    
    public function doadd()
    {
        $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
        if(empty($_POST["description"])){if(!empty($_POST["body"])){$_POST['description']=cut_str($_POST["body"]);}} //description
        $content="";if(!empty($_POST["body"])){$content = $_POST["body"];}
        
		$_POST['shop_id'] = $this->login_info['id']; // 发布者id
        
		//关键词
        if(!empty($_POST["keywords"]))
		{
			$_POST['keywords']=str_replace("，",",",$_POST["keywords"]);
		}
		else
		{
			if(!empty($_POST["title"]))
			{
				$title=$_POST["title"];
				$title=str_replace("，","",$title);
				$title=str_replace(",","",$title);
				$_POST['keywords']=get_keywords($title);//标题分词
			}
		}
        
		if(isset($_POST["dellink"]) && $_POST["dellink"]==1 && !empty($content)){$content=replacelinks($content,array(CMS_BASEHOST));} //删除非站内链接
		$_POST['body']=$content;
		
		//提取第一个图片为缩略图
		if(isset($_POST["autolitpic"]) && $_POST["autolitpic"] && empty($litpic))
		{
			if(getfirstpic($content))
			{
				//获取文章内容的第一张图片
				$imagepath = '.'.getfirstpic($content);
				
				//获取后缀名
				preg_match_all ("/\/(.+)\.(gif|jpg|jpeg|bmp|png)$/iU",$imagepath,$out, PREG_PATTERN_ORDER);
				
				$saveimage='./uploads/'.date('Y/m',time()).'/'.basename($imagepath,'.'.$out[2][0]).'-lp.'.$out[2][0];
				
				//生成缩略图
				$image = \think\Image::open($imagepath);
				// 按照原图的比例生成一个最大为240*180的缩略图
				$image->thumb(CMS_IMGWIDTH, CMS_IMGHEIGHT)->save($saveimage);
				
				//缩略图路径
				$_POST['litpic']='/uploads/'.date('Y/m',time()).'/'.basename($imagepath,'.'.$out[2][0]).'-lp.'.$out[2][0];
			}
		}
		
        $res = $this->getLogic()->add($_POST);
		if($res['code']==ReturnData::SUCCESS)
        {
            $this->success('添加成功！', url('index'));
        }
		
        $this->error($res['msg']);
    }
    
    public function edit()
    {
        if(!empty($_GET["id"])){$id = $_GET["id"];}else{$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit;}
        
        $this->assign('id',$id);
        $where['id'] = $id;
        $where['shop_id'] = $where2['shop_id'] = $this->login_info['id'];
		$this->assign('post',$this->getLogic()->getOne($where));
        
        $count = model('Arctype')->getCount($where2);
        if($count>0){}else{$this->error('请先添加分类', url('shop/Arctype/add'));}
        
        $type_list = model('Arctype')->getAll($where2,['listorder'=>'asc'],['content'],15);
        $this->assign('type_list',$type_list);
        
        return $this->fetch();
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else{$id="";exit;}
        $litpic="";if(!empty($_POST["litpic"])){$litpic = $_POST["litpic"];}else{$_POST['litpic']="";} //缩略图
        if(empty($_POST["description"])){if(!empty($_POST["body"])){$_POST['description']=cut_str($_POST["body"]);}} //description
        $content="";if(!empty($_POST["body"])){$content = $_POST["body"];}
        $_POST['updated_at'] = time();//更新时间
        $where['shop_id'] = $this->login_info['id']; // 发布者id
        $where['id'] = $id;
        
		if(!empty($_POST["keywords"]))
		{
			$_POST['keywords']=str_replace("，",",",$_POST["keywords"]);
		}
		else
		{
			if(!empty($_POST["title"]))
			{
				$title=$_POST["title"];
				$title=str_replace("，","",$title);
				$title=str_replace(",","",$title);
				$_POST['keywords']=get_keywords($title);//标题分词
			}
		}
		
		if(isset($_POST["dellink"]) && $_POST["dellink"]==1 && !empty($content)){$content=replacelinks($content,array(CMS_BASEHOST));} //删除非站内链接
		$_POST['body']=$content;
		
		//提取第一个图片为缩略图
		if(isset($_POST["autolitpic"]) && $_POST["autolitpic"] && empty($litpic))
		{
			if(getfirstpic($content))
			{
				//获取文章内容的第一张图片
				$imagepath = '.'.getfirstpic($content);
				
				//获取后缀名
				preg_match_all ("/\/(.+)\.(gif|jpg|jpeg|bmp|png)$/iU",$imagepath,$out, PREG_PATTERN_ORDER);
				
				$saveimage='./uploads/'.date('Y/m',time()).'/'.basename($imagepath,'.'.$out[2][0]).'-lp.'.$out[2][0];
				
				//生成缩略图
				$image = \think\Image::open($imagepath);
				// 按照原图的比例生成一个最大为240*180的缩略图
				$image->thumb(CMS_IMGWIDTH, CMS_IMGHEIGHT)->save($saveimage);
				
				//缩略图路径
				$_POST['litpic']='/uploads/'.date('Y/m',time()).'/'.basename($imagepath,'.'.$out[2][0]).'-lp.'.$out[2][0];
			}
		}
		
        $res = $this->getLogic()->edit($_POST, $where);
        if($res['code']==ReturnData::SUCCESS)
        {
            $this->success('修改成功！', url('index'), '', 1);
        }
		
        $this->error($res['msg']);
    }
    
    public function del()
    {
        if(!checkIsNumber(input('id',null))){$this->error('参数错误');}
        $where['id'] = input('id');
        $where['shop_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->del($where);
        
		if($res['code'] == ReturnData::SUCCESS)
        {
            $this->success("删除成功");
        }
		
        $this->error($res['msg']);
    }
}