<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;
use app\common\logic\ArticleLogic;

class Article extends Base
{
    public $_logic;
    
	public function _initialize()
	{
        //Token::TokenAuth(request()); //TOKEN验证
        
		parent::_initialize();
        
        $this->_logic = new ArticleLogic();
    }
    
    //列表
    public function index()
	{
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.typeid', '') !== ''){$where['typeid'] = input('param.typeid');}
        if(input('param.keyword', '') !== ''){$where['title'] = ['like','%'.input('param.keyword').'%'];}
        if(input('tuijian', '') !== ''){$where['tuijian'] = input('tuijian');}
        $where['ischeck'] = 0;
        $orderby = input('orderby','');
        
        $res = $this->_logic->getList($where,$orderby,'id,title,typeid,tuijian,click,litpic,pubdate,addtime,description,ischeck',$offset,$limit);
		
        if($res['list'])
        {
            foreach($res['list'] as $k=>$v)
            {
                $res['list'][$k]['url'] = http_host().get_front_url(array("id"=>$v['id'],"type"=>'content'));
                if(!empty($v['litpic'])){$res['list'][$k]['litpic'] = http_host().$v['litpic'];}
            }
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //详情
    public function detail()
	{
        //参数
        $where['id'] = input('param.id','');
        
        if($where['id'] == ''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
		$res = $this->_logic->getOne($where);
        if(!$res){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(!empty($res['litpic'])){$res['litpic'] = http_host().$res['litpic'];}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    //添加
    public function add()
    {
        if(IS_POST)
        {
            $res = $this->_logic->add($_POST);
            
            exit(json_encode($res));
        }
    }
    
    //修改
    public function edit()
    {
        if(input('id','')!=''){$id = input('id');}else{$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(IS_POST)
        {
            unset($_POST['id']);
            $where['id'] = $id;
            
            $res = $this->_logic->edit($_POST,$where);
            
            exit(json_encode($res));
        }
    }

    //删除
    public function del()
    {
        if(input('id','')!=''){$id = input('id');}else{$id="";}if(preg_match('/[0-9]*/',$id)){}else{exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(IS_POST)
        {
            unset($_POST['id']);
            $where['id'] = $id;
            
            $res = $this->_logic->del($where);
            
            exit(json_encode($res));
        }
    }
}