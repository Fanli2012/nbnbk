<?php
namespace app\fladmin\controller;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ArticleTypeLogic;

class ArticleType extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new ArticleTypeLogic();
    }
    
    //列表
    public function index()
    {
        $list = $this->tree_to_list($this->list_to_tree());
        $this->assign('list',$list);
        
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
        
        $parent_id = input('parent_id',0);
        if($parent_id!=0)
        {
            if(preg_match('/[0-9]*/',$parent_id)){}else{$this->error('参数错误');}
            $this->assign('parent_article',model('Arctype')->getOne("id=$parent_id",['content']));
        }
        
        $this->assign('parent_id',$parent_id);
        
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
        
        $reid = input('reid',0);
        if($reid!=0){$this->assign('parent_article',$this->getLogic()->getOne("id=$reid"));}
        
        return $this->fetch();
    }
	
    //删除
    public function del()
    {
        if(!checkIsNumber(input('id',null))){$this->error('删除失败！请重新提交');}
        $where['id'] = input('id');
        
        if($this->getLogic()->getOne(['parent_id'=>$where['id']]))
		{
			$this->error('删除失败！请先删除子栏目');
		}
		
        $res = $this->getLogic()->del($where);
		if($res['code'] == ReturnData::SUCCESS)
        {
            if(model('Article')->getCount(['type_id'=>$id])>0) //判断该分类下是否有文章，如果有把该分类下的文章也一起删除
            {
                if(model('Article')->del(['type_id'=>$id]))
                {
                    $this->success('删除成功', url('index'), '',1);
                }
                
                $this->error('栏目下的文章删除失败');
            }
            
            $this->success('删除成功', url('index'), '', 1);
        }
		
        $this->error($res['msg']);
    }
    
    
    
    public function doadd()
    {
        if(!empty($_POST["prid"])){if($_POST["prid"]=="top"){$_POST['parent_id']=0;}else{$_POST['parent_id'] = $_POST["prid"];}}//父级栏目id
        $_POST['add_time'] = time();//添加时间
		unset($_POST["prid"]);
		
		if(db('arctype')->insert($_POST))
        {
            $this->success('添加成功', CMS_ADMIN.'Category' , 1);
        }
		else
		{
			$this->error('添加失败！请修改后重新添加', CMS_ADMIN.'Category' , 3);
		}
    }
    
    public function doedit()
    {
        if(!empty($_POST["id"])){$id = $_POST["id"];unset($_POST["id"]);}else {$id="";exit;}
        $_POST['add_time'] = time();//添加时间
        
		if(db('arctype')->where("id=$id")->update($_POST))
        {
            $this->success('修改成功', CMS_ADMIN.'Category' , 1);
        }
		else
		{
			$this->error('修改失败！请修改后重新添加', CMS_ADMIN.'Category/edit?id='.$id , 3);
		}
    }
    
	/**
     * 将列表生成树形结构
     * @param int $parent_id 父级ID
     * @param int $deep 层级
     * @return array
     */
	public function list_to_tree($parent_id=0,$deep=0)
	{
		$arr=array();
		
		$cats = model('ArticleType')->getAll(['parent_id'=>$parent_id], 'listorder asc');
		if($cats)
		{
			foreach($cats as $row)//循环数组
			{
				$row['deep'] = $deep;
                //如果子级不为空
				if($child = $this->list_to_tree($row["id"],$deep+1))
				{
					$row['child'] = $child;
				}
				$arr[] = $row;
			}
		}
        
        return $arr;
	}
    
    /**
     * 树形结构转成列表
     * @param array $list 数据
     * @param int $parent_id 父级ID
     * @return array
     */
	public function tree_to_list($list,$parent_id=0)
	{
		global $temp;
		if(!empty($list))
		{
			foreach($list as $v)
			{
				$temp[] = array("id"=>$v['id'],"deep"=>$v['deep'],"name"=>$v['name'],"filename"=>$v['filename'],"parent_id"=>$v['parent_id'],"add_time"=>$v['add_time']);
				//echo $v['id'];
				if(isset($v['child']))
				{
					$this->tree_to_list($v['child'],$v['parent_id']);
				}
			}
		}
		
		return $temp;
	}
}