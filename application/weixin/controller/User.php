<?php
namespace app\weixin\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
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
    
    //列表
    public function index()
	{
		$pagesize = 10;
        $offset = 0;
        
		$id = input('id');
        //文章分类
        $postdata = array(
            'id'  => $id
		);
        $url = sysconfig('CMS_API_URL').'/article_type/detail';
		$arctype_detail = curl_request($url,$postdata,'GET');
        $assign_data['post'] = $arctype_detail['data'];
        
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        
        //文章列表
        $postdata2 = array(
            'limit'   => $pagesize,
            'offset'  => $offset,
            'type_id' => $id
		);
        $url = sysconfig('CMS_API_URL').'/article/index';
		$res = curl_request($url, $postdata2, 'GET');
        if($res['data']['list'])
        {
            foreach($res['data']['list'] as $k => $v)
            {
                $res['data']['list'][$k]['update_time'] = date('Y-m-d H:i', $v['update_time']);
            }
        }
        $assign_data['list'] = $res['data']['list'];
        
        $assign_data['totalpage'] = ceil($res['data']['count']/$pagesize);
        
        if(isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax']==1)
        {
    		$html = '';
            
            if($res['data']['list'])
            {
                foreach($res['data']['list'] as $k => $v)
                {
                    $html .= '<li><a href="'.url('detail').'?id='.$v['id'].'">'.$v['title'].'</a><p>'.$v['update_time'].'</p></li>';
                }
            }
            
    		exit(json_encode($html));
    	}
		//dd($assign_data);
		$this->assign($assign_data);
        return $this->fetch();
    }
	
    //详情
    public function detail()
	{
        if(!checkIsNumber(input('id',null))){Helper::http404();}
        $id = input('id');
		
        $postdata = array(
            'id'  => $id
		);
        $url = sysconfig('CMS_API_URL').'/article/detail';
		$res = curl_request($url,$postdata,'GET');
        if(empty($res['data'])){Helper::http404();}
        $res['data']['content'] = preg_replace('/src=\"\/uploads\/allimg/',"src=\"".sysconfig('CMS_BASEHOST')."/uploads/allimg",$res['data']['content']);
        $res['data']['update_time'] = date('Y-m-d',$res['data']['update_time']);
        $assign_data['post'] = $res['data'];
		//dd($assign_data['post']);
		$this->assign($assign_data);
        return $this->fetch();
    }
    
    /**
     * 获取文章上一篇，下一篇
     * @param int $param['article_id'] 当前文章id
     * @return array
     */
    public function getPreviousNextArticle(array $param)
    {
        $res['previous_article'] = [];
        $res['next_article'] = [];
        
        $where['id'] = $param['article_id'];
        $post = model('Article')->getOne($where,['content']);
        if(!$post)
        {
            return $res;
        }
        $res['previous_article'] = model('Article')->getOne(['id'=>['<',$param['article_id']],'type_id'=>$post['type_id']],['content'],'id desc');
        $res['next_article'] = model('Article')->getOne(['id'=>['>',$param['article_id']],'type_id'=>$post['type_id']],['content'],'id asc');
        return $res;
    }
}