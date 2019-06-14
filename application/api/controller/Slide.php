<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\SlideLogic;
use app\common\model\Slide as SlideModel;

class Slide extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new SlideLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        if(input('keyword', '') !== ''){$where['title'] = ['like','%'.input('keyword').'%'];}
        if(input('target', '') !== ''){$where['target'] = input('target');}
        if(input('group_id', '') !== ''){$where['group_id'] = input('group_id');}
		if(input('status', '') === ''){$where['status'] = SlideModel::SLIDE_STATUS_NORMAL;}else{if(input('status') != -1){$where['status'] = input('status');}}
        $orderby = input('orderby','listorder asc');
        
        $res = $this->getLogic()->getList($where, $orderby, '*', $offset, $limit);
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if($v['pic']){$res['list'][$k]['pic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['pic'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $where['id'] = input('id');
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if($res['pic']){$res['pic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$res['pic'];}
        
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