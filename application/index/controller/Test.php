<?php
namespace app\index\controller;
use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ShopLogic;

class Test extends Base
{
    //首页
    public function index()
	{
        Log::error('错误信息');Log::info('首页');
        $pagesize = 5;
        $offset = 0;
        if(isset($_REQUEST['page'])){$offset = ($_REQUEST['page']-1)*$pagesize;}
        $where['status'] = 0;
        $where['delete_time'] = 0;
        $where['add_time'] = ['<',time()];
		$res = logic('Article')->getList($where, 'id desc', ['content'], $offset, $pagesize);
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
                    if(!empty($v['litpic'])){$html .= '<a class="limg" href="'.model('Article')->getArticleDetailUrl(array('id'=>$v['id'])).'"><img alt="'.$v['title'].'" src="'.$v['litpic'].'"></a>';}
                    $html .= '<strong class="tit"><a href="'.model('Article')->getArticleDetailUrl(array('id'=>$v['id'])).'" target="_blank">'.$v['title'].'</a></strong><p>'.mb_strcut($v['description'],0,150,'UTF-8').'..</p>';
                    $html .= '<div class="info"><span class="fl"><em>'.date("m-d H:i",$v['update_time']).'</em></span><span class="fr"><em>'.$v['click'].'</em>人阅读</span></div>';
                    $html .= '<div class="cl"></div></div>';
                }
            }
            
    		exit(json_encode($html));
    	}
        
        //推荐文章
        $relate_tuijian_list = cache("index_index_index_relate_tuijian_list");
        if(!$relate_tuijian_list)
        {
            $where2['delete_time'] = 0;
            $where2['status'] = 0;
            $where2['tuijian'] = 1;
            $where2['add_time'] = ['<',time()];
            //$where2['add_time'] = ['>',(time()-30*3600*24)];
            $relate_tuijian_list = logic('Article')->getAll($where2, 'update_time desc', ['content'], 5);
            cache("index_index_index_relate_tuijian_list",$relate_tuijian_list,3600); //1小时
        }
        $this->assign('relate_tuijian_list',$relate_tuijian_list);
        
        //随机文章
        $relate_rand_list = cache("index_index_index_relate_rand_list");
        if(!$relate_rand_list)
        {
            $where_rand['delete_time'] = 0;
            $where_rand['status'] = 0;
            $where_rand['add_time'] = ['<',time()];
            $relate_rand_list = logic('Article')->getAll($where_rand, ['orderRaw','rand()'], ['content'], 5);
            cache("index_index_index_relate_rand_list",$relate_rand_list,3600); //1小时
        }
        $this->assign('relate_rand_list',$relate_rand_list);
        
        //标签
        $relate_tag_list = cache("index_index_index_relate_tag_list");
        if(!$relate_tag_list)
        {
            $where_tag['status'] = 0;
            $relate_tag_list = logic('Tag')->getAll($where_tag, 'id desc', ['content'], 5);
            cache("index_index_index_relate_tag_list",$relate_tag_list,3600); //1小时
        }
        $this->assign('relate_tag_list',$relate_tag_list);
        
        //友情链接
        $friendlink_list = cache("index_index_index_friendlink_list");
        if(!$friendlink_list)
        {
            $friendlink_list = logic('Friendlink')->getAll('', 'id desc', '*', 5);
            cache("index_index_index_friendlink_list",$friendlink_list,604800); //7天
        }
        $this->assign('friendlink_list',$friendlink_list);
        
        //轮播图
        $slide_list = cache("index_index_index_slide_list");
        if(!$slide_list)
        {
            $where_slide['status'] = 0;
            $slide_list = logic('Slide')->getAll($where_slide, 'listorder asc', '*', 5);
            cache("index_index_index_slide_list",$slide_list,86400); //1天
        }
        $this->assign('slide_list',$slide_list);
        
        return $this->fetch();
    }
	
    //图片上传
    public function formUploadimg()
	{
        return $this->fetch();
	}
    
    //文章详情页
    public function detail()
	{
        $id=input('id');
        if(empty($id) || !preg_match('/[0-9]+/',$id)){Helper::http404();}
        $article = db('article');
		
		if(cache("detailid$id")){$post=cache("detailid$id");}else{$post = db('article')->where("id=$id")->find();if(empty($post)){Helper::http404();}$post['name'] = db('arctype')->where("id=".$post['typeid'])->value('name');cache("detailid$id",$post,2592000);}
		if($post)
        {
			$cat=$post['typeid'];
            $post['body']=ReplaceKeyword($post['body']);
            if(!empty($post['writer'])){$post['writertitle']=$post['title'].' '.$post['writer'];}
            
			$this->assign('post',$post);
            $pre = get_article_prenext(array('aid'=>$post["id"],'typeid'=>$post["typeid"],'type'=>"pre"));
            $this->assign('pre',$pre);
        }
        else{Helper::http404();}
        
        //获取最新列表
        $where = '';
        if($pre){$where['typeid']=$post['typeid'];$where['id']=array('lt',$pre['id']);}
        $latest_posts = $article->where($where)->field('body',true)->order('id desc')->limit(5)->select();
        if(!$latest_posts){$latest_posts = $article->field('body',true)->order('id desc')->limit(5)->select();}
        $this->assign('latest_posts',$latest_posts);
        
		if(cache("catid$cat")){$post=cache("catid$cat");}else{$post = db('arctype')->where("id=$cat")->find();cache("catid$cat",$post,2592000);}
        
        return $this->fetch($post['temparticle']);
    }
	
    //标签详情页，共有3种显示方式，1正常列表，2列表显示文章，3显示描述
	public function tag()
	{
        $tag=input('tag');
        $pagenow=input('page');
        
		if(empty($tag) || !preg_match('/[0-9]+/',$tag)){Helper::http404();}
        
		$post = db('tagindex')->where("id=$tag")->find();
        $this->assign('post',$post);
		
		$counts=db("taglist")->where("tid=$tag")->count('aid');
		if($counts>sysconfig('CMS_LIST_MAX_TOTAL')){$counts=sysconfig('CMS_BASEHOST');}
		$pagesize=sysconfig('CMS_PAGESIZE');$page=0;
		if($counts % $pagesize){//取总数据量除以每页数的余数
		$pages = intval($counts/$pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
		}else{$pages = $counts/$pagesize;}
		if(!empty($pagenow)){if($pagenow==1 || $pagenow>$pages){Helper::http404();}$page = $pagenow-1;$nextpage=$pagenow+1;$previouspage=$pagenow-1;}else{$page = 0;$nextpage=2;$previouspage=0;}
		$this->assign('page',$page);
		$this->assign('pages',$pages);
		$this->assign('counts',$counts);
		$start=$page*$pagesize;
		
		$posts=db("taglist")->where("tid=$tag")->order('aid desc')->limit("$start,$pagesize")->select();
		foreach($posts as $row)
		{
			$aid[] = $row["aid"];
		}
		$aid = isset($aid)?implode(',',$aid):"";
		
        if($aid!="")
        {
            if($post['template']=='tag2')
            {
                $this->assign('posts',arclist(array("sql"=>"id in ($aid)","orderby"=>"id desc","limit"=>"$pagesize","field"=>"title,body"))); //获取列表
            }
            else
            {
                $this->assign('posts',arclist(array("sql"=>"id in ($aid)","orderby"=>"id desc","limit"=>"$pagesize"))); //获取列表
            }
        }
		else
        {
            $this->assign('posts',""); //获取列表
        }
        
		$this->assign('pagenav',get_listnav(array("counts"=>$counts,"pagesize"=>$pagesize,"pagenow"=>$page+1,"catid"=>$tag,"urltype"=>"tag"))); //获取分页列表
		
		return $this->fetch($post['template']);
    }
    
	//标签页
    public function tags()
	{
		return $this->fetch();
    }
    
    //推荐页
	public function tuijian()
	{
        $pagenow=input('page');
        $where['tuijian'] = 1;
        
		$counts=db("article")->where($where)->count();
		if($counts>sysconfig('CMS_LIST_MAX_TOTAL')){$counts=sysconfig('CMS_BASEHOST');}
		$pagesize=sysconfig('CMS_PAGESIZE');$page=0;
		if($counts % $pagesize){//取总数据量除以每页数的余数
		$pages = intval($counts/$pagesize) + 1; //如果有余数，则页数等于总数据量除以每页数的结果取整再加一,如果没有余数，则页数等于总数据量除以每页数的结果
		}else{$pages = $counts/$pagesize;}
		if(!empty($pagenow)){if($pagenow==1 || $pagenow>$pages){Helper::http404();}$page = $pagenow-1;$nextpage=$pagenow+1;$previouspage=$pagenow-1;}else{$page = 0;$nextpage=2;$previouspage=0;}
		$this->assign('page',$page);
		$this->assign('pages',$pages);
		$this->assign('counts',$counts);
		$start=$page*$pagesize;
        
        $posts = db('article')->where($where)->field('body',true)->order('id desc')->limit("$start,$pagesize")->select();
		$this->assign('posts',$posts); //获取列表
        $pagenav = '';if($nextpage<=$pages && $nextpage>0){$pagenav = get_pagination_url(http_host().'/tuijian',$_SERVER['QUERY_STRING'],$nextpage);} //获取上一页下一页网址
		$this->assign('pagenav',$pagenav);
        
		return $this->fetch();
    }
    
    //XML地图
    public function sitemap()
    {
        //最新文章
        $where['delete_time'] = 0;
        $where['status'] = 0;
        $where['add_time'] = ['<',time()];
        $list = logic('Article')->getAll($where, 'update_time desc', ['content'], 100);
        $this->assign('list',$list);
        
		return $this->fetch();
    }
    
    //404页面
    public function notfound()
    {
		return $this->fetch();
    }
    
	public function test()
    {
        //echo '<pre>';print_r(request());exit;
		//echo (dirname('/images/uiui/1.jpg'));
		//echo '<pre>';
		//$str='<p><img border="0" src="./images/1.jpg" alt=""/></p>';
		
		//echo getfirstpic($str);
		//$imagepath='.'.getfirstpic($str);
		//$image = new \Think\Image(); 
		//$image->open($imagepath);
		// 按照原图的比例生成一个最大为240*180的缩略图并保存为thumb.jpg
		//$image->thumb(CMS_IMGWIDTH, CMS_IMGHEIGHT)->save('./images/1thumb.jpg');
        
        return $this->fetch();
    }
	
	public function img()
    {
        return $this->fetch();
    }
	
	public function aaa()
    {
        $data = $this->base64_to_blob('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAB0QAAAHRCAYAAAD69Ap1AAAgAElEQVR4XuzdCZglVXk/4F/1DIxKxBiiRmMQl6gRNW6IiiBIjCaICxgTjRrQRGNQAkxXD2gSG6PIdDXgHwnRJIomRuIGEYgYo0JkEVxxiytRMW5xgSCIA0zX/6npGZ2+985Md09333vrvvU8/fTM7VPf+b73FNoPH+dUERcBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaKlC0tC5lESBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAIBqiHgICBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBForoCHa2qVVGAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECGqKeAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEWiugIdrapVUYAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIaop4BAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgRaK6Ah2tqlVRgBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAhqingECBAgQIDAyAtUrZkst9pr9Xn999nt54sgQKJQAAQIECBAgQIAAAQIECBAgQIAAgZET0BAduSVXMAECBAiMrsCmhuhkR/2TGqL9eCKatZhZk6y6a1I3DeqLZ7PQnO7HapiTAAECBAgQIECAAAECBAgQIECg3QIaou1eX9URIECAAIGtBKbekRS/N5ekfmcy8UxMKy2gOb3S4uYjQIAAAQIECBAgQIAAAQIECBAYXQEN0dFde5UTIECAwMgJVBclObCj7IuT8qCRo+h7wT3X4hNJ+Yi+pyYBAgQIECBAgAABAgQIECBAgAABAi0T0BBt2YIqhwABAgQIbFtAQ3Rwno7ps5L6iB75HJaU5w5OnjIhQIAAAQIECBAgQIAAAQIECBAgMPwCGqLDv4YqIECAAAEC8xTQEJ0n1AoMW39SMnZCj4luTIpHJONfXIEkTEGAAAECBAgQIECAAAECBAgQIEBgJAQ0REdimRVJgAABAgQaAe+tHJznYNNaPCrJk3rk9LGkfOTg5CoTAgQIECBAgAABAgQIECBAgAABAsMtoCE63OsnewIECBAgsAABDdEFYK3Q0OnPJPWDuierp5KJdSuUhGkIECBAgAABAgQIECBAgAABAgQItFpAQ7TVy6s4AgQIECCwtYCG6OA9D6fvntz8g6TepTu34k+S8X8YvJxlRIAAAQIECBAgQIAAAQIECBAgQGC4BDREh2u9ZEuAAAECBHZCYP3rkrGXzA0w85pk3ct2Iqhbd1qgOjTJeT3CbEjy5KT8wE5PIQABAgQIECBAgAABAgQI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AgQIECBAgAABAgQIECBAgAABAqsQUIiuAs0jBAgQIECAwEsJKETtBgECBAgQIECAAAECBAgQIECAAAECkyWgEJ2sebgNAQIECBCYcoH29CR7h0JsS5p9Ux7M9QkQIECAAAECBAgQIECAAAECBAgQmFIBheiUDs61CRAgQIDA5Aq0/UK0X4z2/+xLmm2Te1c3I0CAAAECBAgQIECAAAECBAgQIECguoBCtPqE5SNAgAABAhsiML/9wGvnbt+Q13spAQIECBAgQIAAAQIECBAgQIAAAQIEnhdQiFoFAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBCXOikzAAARiklEQVQgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQEAhagcIECBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECgroBAtO1rBCBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBBQiNoBAgQIECBAgAABAgQIECBAgAABAgQIECBAgAABAgTKCihEy45WMAIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIEFKJ2gAABAgQIECBAgAABAgQIECBAgAABAgQIECBAgACBsgIK0bKjFYwAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAYWoHSBAgAABAgQIECBAgAABAgQIECBAgAABAgQIECBAoKyAQrTsaAUjQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQOA/0haod820bJ4AAAAASUVORK5CYII=');
		header('Location: '.$data['type']);
		echo $data['blob'];
    }
	
	public function base64_to_blob($base64Str){
		if($index = strpos($base64Str,'base64,',0)){
			$blobStr = substr($base64Str,$index+7);
			$typestr = substr($base64Str,0,$index);
			preg_match("/^data:(.*);$/",$typestr,$arr);
			return ['blob'=>base64_decode($blobStr),'type'=>$arr[1]];
		}
		return false;
	}
	
}