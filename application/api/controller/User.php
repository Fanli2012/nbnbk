<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
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
    
    //我的分销团队
    public function myteam()
	{
        //参数
        $limit = input('limit/d', 10);
        $offset = input('offset/d', 0);
        $where = array();
		$where['parent_id'] = $this->login_info['id'];
        if(input('sex', '') !== ''){$where['sex'] = input('sex');}
        if(input('group_id', '') !== ''){$where['group_id'] = input('group_id');}
		if(input('status', '') === ''){$where['status'] = UserModel::USER_STATUS_NORMAL;}else{if(input('status') != -1){$where['status'] = input('status');}}
        $orderby = input('orderby','id desc');
        if($orderby=='rand()'){$orderby = array('orderRaw','rand()');}
        
        $res = $this->getLogic()->getList($where,$orderby,'parent_id,mobile,nickname,user_name,head_img,sex,commission,consumption_money,user_rank,status,add_time',$offset,$limit);
        if($res['count']>0)
        {
            foreach($res['list'] as $k=>$v)
            {
                if(!empty($v['head_img'])){$res['list'][$k]['head_img'] = (substr($v['head_img'], 0, strlen('http')) === 'http') ? $v['head_img'] : sysconfig('CMS_SITE_CDN_ADDRESS').$v['head_img'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        $where['id'] = $this->login_info['id'];
        
		$res = $this->getLogic()->getUserInfo($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::RECORD_NOT_EXIST)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
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
    
}