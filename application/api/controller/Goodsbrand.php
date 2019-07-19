<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\GoodsBrandLogic;
use app\common\model\GoodsBrand as GoodsBrandModel;

class Goodsbrand extends Common
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new GoodsBrandLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        $orderby = input('orderby','listorder asc');
        if(input('parent_id', null) != null){$where['parent_id'] = input('parent_id');}
		if(input('status', '') === ''){$where['status'] = GoodsBrandModel::GOODS_BRAND_STATUS_NORMAL;}else{if(input('status') != -1){$where['status'] = input('status');}}
        
        $res = $this->getLogic()->getList($where,$orderby,['content'],$offset,$limit);
		if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if($v['litpic']){$res['list'][$k]['litpic'] = sysconfig('CMS_SITE_CDN_ADDRESS').$v['litpic'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $where['id'] = input('id');
        
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
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