<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Token;
use app\common\lib\ReturnData;

class Article extends Base
{
	public function _initialize()
	{
        //Token::TokenAuth(request()); //TOKEN验证
        
		parent::_initialize();
    }
    
    public function index()
	{
        //参数
        $limit = input('param.limit',10);
        $offset = input('param.offset', 0);
        if(input('param.typeid', '') !== ''){$data['typeid'] = input('param.typeid');}
        if(input('param.keyword', '') !== ''){$data['title'] = ['like','%'.input('param.keyword').'%'];}
        if(input('tuijian', '') !== ''){$data['tuijian'] = input('tuijian');}
        $data['ischeck'] = 0;
        $orderby = input('orderby','');
        
        $res = db('article')->where($data)->field('body',true)->order($orderby)->limit("$offset,$limit")->select();
		
        foreach($res as $k=>$v)
        {
            $res[$k]['pubdate'] = date('Y-m-d',$v['pubdate']);
            $res[$k]['addtime'] = date('Y-m-d',$v['addtime']);
            $res[$k]['url'] = http_host().get_front_url(array("id"=>$v['id'],"type"=>'content'));
            if(!empty($v['litpic'])){$res[$k]['litpic'] = http_host().$v['litpic'];}
        }
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
    
    public function detail()
	{
        //参数
        $data['id'] = input('param.id','');
        
        if($data['id'] == ''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        $res = db('article')->where($data)->find();
		
        $res['pubdate'] = date('Y-m-d',$res['pubdate']);
        $res['addtime'] = date('Y-m-d',$res['addtime']);
        if(!empty($res['litpic'])){$res['litpic'] = http_host().$res['litpic'];}
        
        db('article')->where($data)->setInc('click', 1);
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS,$res)));
    }
}