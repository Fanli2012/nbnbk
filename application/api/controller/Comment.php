<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\CommentLogic;
use app\common\logic\Comment as CommentModel;

class Comment extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new CommentLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $where = array();
        $limit = input('limit',10);
        $offset = input('offset', 0);
        $orderby = input('orderby','id desc');
        $where['comment_type'] = 0;if(input('comment_type','')!=''){$where['comment_type'] = input('comment_type');}; //0商品评价，1文章评价
        if(input('comment_rank', '') != ''){$where['comment_rank'] = input('comment_rank');}
        if(input('id_value', '') != ''){$where['id_value'] = input('id_value');}
        if(input('parent_id', '') != ''){$where['parent_id'] = input('parent_id');}
        $where['status'] = Comment::SHOW_COMMENT;
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
    
    //添加
    public function add()
    {
        if(Helper::isPostRequest())
        {
            $_POST['ip_address'] = Helper::getRemoteIp();
			$_POST['user_id'] = $this->login_info['id'];
            $res = $this->getLogic()->add($_POST);
            
            exit(json_encode($res));
        }
    }
    
    //批量添加商品评论
    public function batch_add_goods_comment(Request $request)
	{
        if(input('comment','')=='' || input('order_id','')==''){return ReturnData::create(ReturnData::PARAMS_ERROR);}
        $comment = json_decode(input('comment'), true);
        if($comment)
        {
			$time = time();
            foreach($comment as $k=>$v)
            {
                $comment[$k]['user_id'] = $this->login_info['id'];
                $comment[$k]['ip_address'] = Helper::getRemoteIp();
                $comment[$k]['add_time'] = $time;
                $comment[$k]['order_id'] = input('order_id');
            }
        }
        
		$res = $this->getLogic()->batchAddGoodsComment($comment);
        exit(json_encode($res));
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
}