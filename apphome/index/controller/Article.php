<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ArticleLogic;

class Article extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new ArticleLogic();
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
        
        $where['id'] = $id;
        $post = $this->getLogic()->getOne($where);
        if(!$post){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
        
        $post['body']=ReplaceKeyword($post['body']);
        
        $this->assign('post',$post);
        
        //随机文章
        $where2['delete_time'] = 0;
        $rand_posts = logic('Article')->getAll($where2, 'rand()', ['content'], 5);
        $this->assign('rand_posts',$rand_posts);
        
        //最新文章
        $where3['delete_time'] = 0;
        $where2['typeid'] = $post['typeid'];
        $zuixin_posts = logic('Article')->getAll($where3, 'id desc', ['content'], 5);
        $this->assign('zuixin_posts',$zuixin_posts);
        
        return $this->fetch();
    }
}