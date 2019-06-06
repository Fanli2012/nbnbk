<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\PageLogic;
use app\common\model\Page as PageModel;

class Page extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new PageLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
		$where = array();
        if(input('keyword', '') !== ''){$where['title'] = ['like','%'.input('keyword').'%'];}
		if(input('group_id', '') !== ''){$where['group_id'] = input('group_id');}
        $orderby = input('orderby','id desc');
        
        $res = $this->getLogic()->getList($where, $orderby, ['content'], $offset, $limit);
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if(!empty($v['litpic'])){$res['list'][$k]['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['litpic'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        $where = array();
        if(input('id', '') !== ''){$where['id'] = input('id');}
        if(input('filename', '') !== ''){$where['filename'] = input('filename');}
        if(!$where){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(!empty($res['litpic'])){$res['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$res['litpic'];}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $res = $this->getLogic()->add($_POST);
            
            exit(json_encode($res));
        }
    }
    
    //修改
    public function edit()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            unset($_POST['id']);
            
            $res = $this->getLogic()->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            
            $res = $this->getLogic()->del($where);
            
            exit(json_encode($res));
        }
    }
}