<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\TagLogic;

class Tag extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new TagLogic();
    }
    
    //列表
    public function index()
	{
        $where = [];
        $title = '';
        
        $key = input('key', null);
        if($key != null)
        {
            $arr_key = $this->getArrByString($key);
            if(!$arr_key){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
            
            //分类id
            if(isset($arr_key['f']) && !empty($arr_key['f']))
            {
                $where['typeid'] = $arr_key['f'];
                
                $post = db('arctype')->where(['id'=>$arr_key['f']])->find();
                $this->assign('post',$post);
            }
        }
        
        $where['delete_time'] = 0;
        $where['is_check'] = 0;
        $posts = $this->getLogic()->getPaginate($where, 'id desc', ['body']);
        if(!$posts){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
        
        $page = $posts->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $this->assign('posts', $posts);
        
        //最新
        $where2['delete_time'] = 0;
        $where2['is_check'] = 0;
        $zuixin_list = logic('article')->getAll($where2, 'id desc', ['body'], 5);
        $this->assign('zuixin_list',$zuixin_list);
        
        //推荐
        $where3['delete_time'] = 0;
        $where3['is_check'] = 0;
        $where3['tuijian'] = 1;
        $where3['litpic'] = ['<>',''];
        $tuijian_list = logic('article')->getAll($where3, 'id desc', ['body'], 5);
        $this->assign('tuijian_list',$tuijian_list);
        
        //seo标题设置
        $title = $title.'最新动态';
        $this->assign('title',$title);
        return $this->fetch();
    }
	
    //字符串转成数组
    public function getArrByString($key)
	{
        $res = array();
        
        if(!$key){return [];}
        
        preg_match_all('/[a-z]+/u' , $key, $letter);
        preg_match_all('/[0-9]+/u' , $key, $number);
        if(count($letter[0]) != count($number[0])){return [];}
        
        foreach($letter[0] as $k=>$v)
        {
            $res[$v] = $number[0][$k];
        }
        
        return $res;
    }
    
    //文章详情页
    public function detail()
	{
        if(!checkIsNumber(input('id',null))){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
        $id = input('id');
        
        $where['fl_taglist.tag_id'] = $id;
        
        $pagesize = 11;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        $where['status'] = 0;
        $where['delete_time'] = 0;
		$res = logic('Taglist')->getJoinList($where, 'fl_article.update_time desc', 'fl_article.*', $offset, $pagesize);
        if($res['list'])
        {
            foreach($res['list'] as $k => $v)
            {
                
            }
        }
        $this->assign('list',$res['list']);
        $totalpage = ceil($res['count']/$pagesize);
        $this->assign('totalpage',$totalpage);
        if(isset($_REQUEST['page_ajax']) && $_REQUEST['page_ajax']==1)
        {
    		$html = '';
            if($res['list'])
            {
                foreach($res['list'] as $k => $v)
                {
                    $html .= '<div class="list">';
                    if(!empty($v['litpic'])){$html .= '<a class="limg" href="/p/'.$v['id'].'"><img alt="'.$v['title'].'" src="'.$v['litpic'].'"></a>';}
                    $html .= '<strong class="tit"><a href="/p/'.$v['id'].'" target="_blank">'.$v['title'].'</a></strong><p>'.mb_strcut($v['description'],0,150,'UTF-8').'..</p>';
                    $html .= '<div class="info"><span class="fl"><em>'.date("m-d H:i",$v['update_time']).'</em></span><span class="fr"><em>'.$v['click'].'</em>人阅读</span></div>';
                    $html .= '<div class="cl"></div></div>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
        //推荐文章
        $where2['status'] = 0;
        $where2['delete_time'] = 0;
        //$where2['add_time'] = ['>',(time()-30*3600*24)];
        $article_tj_list = logic('Article')->getAll($where2, 'click desc', ['content'], 5);
        $this->assign('article_tj_list',$article_tj_list);
        
        //随机文章
        $where3['status'] = 0;
        $where3['delete_time'] = 0;
        $article_rand_list = logic('Article')->getAll($where3, 'rand()', ['content'], 5);
        $this->assign('article_rand_list',$article_rand_list);
        
        return $this->fetch();
    }
}