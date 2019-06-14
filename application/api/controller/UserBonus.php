<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\UserBonusLogic;
use app\common\logic\UserBonus as UserBonusModel;

class UserBonus extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new UserBonusLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        $orderby = input('orderby','bonus_money desc');
		if(input('status', '') === ''){$where['status'] = UserBonusModel::USER_BONUS_STATUS_UNUSED;}else{if(input('status') != -1){$where['status'] = input('status');}}
        $where['user_id'] = $this->login_info['id'];
		
        $res = $this->getLogic()->getList($where, $orderby, '*', $offset, $limit);
		
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $where['id'] = input('id');
        $where['user_id'] = $this->login_info['id'];
		
		$res = $this->getLogic()->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::RECORD_NOT_EXIST)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //用户获取优惠券
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
            if(!checkIsNumber(input('id/d',0))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
            $where['id'] = input('id');
            $where['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->del($where);
            
            exit(json_encode($res));
        }
    }
	
    public function userAvailableBonusList(Request $request)
	{
        //参数
        $data['user_id'] = $this->login_info['id'];
        
        $data['min_amount'] = input('min_amount','');
        if($data['min_amount']==''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        $res = $this->getLogic()->getAvailableBonusList($data);
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
}