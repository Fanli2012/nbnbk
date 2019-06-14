<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\UserGoodsCollectLogic;
use app\common\logic\UserGoodsCollect as UserGoodsCollectModel;

class UserGoodsCollect extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserGoodsCollectLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        $orderby = input('orderby','add_time desc');
        $where['user_id'] = $this->login_info['id'];
		
        $res = $this->getLogic()->getList($where, $orderby, '*', $offset, $limit);
		
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //详情
    public function detail()
	{
        //参数
		$where = array();
		if(input('id', '')!==''){$where['id'] = input('id');}
		if(input('goods_id', '')!==''){$where['goods_id'] = input('goods_id');}
		if(!$where){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
		
        $where['user_id'] = $this->login_info['id'];
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::RECORD_NOT_EXIST)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
			$_POST['user_id'] = $this->login_info['id'];
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
			$where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }
    
    //删除
    public function del()
    {
        if(Helper::isPostRequest())
        {
            if(input('id', '')!==''){$where['id'] = input('id');}
            if(input('goods_id', '')!==''){$where['goods_id'] = input('goods_id');}
            $where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->del($where);
            
            exit(json_encode($res));
        }
    }
}