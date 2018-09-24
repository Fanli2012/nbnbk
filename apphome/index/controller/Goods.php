<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\GoodsLogic;

class Goods extends Base
{
    public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new GoodsLogic();
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
            
            //省
            if(isset($arr_key['p']) && !empty($arr_key['p']))
            {
                $where['fl_shop.province_id'] = $arr_key['p'];
                $title = model('Region')->getRegionName($where['fl_shop.province_id']);
                $this->assign('province',$title);
                
                $province_id = $arr_key['p'];
            }
            
            //市
            if(isset($arr_key['c']) && !empty($arr_key['c']))
            {
                $where['fl_shop.city_id'] = $arr_key['c'];
                $region = model('Region')->getOne(['id'=>$where['fl_shop.city_id']]);
                if($region)
                {
                    $title = $region['name'];
                    $this->assign('city',$region['name']);
                    $this->assign('province',model('Region')->getRegionName($region['parent_id']));
                    
                    $province_id = $region['parent_id'];
                }
            }
            
            //区
            if(isset($arr_key['d']) && !empty($arr_key['d']))
            {
                $where['fl_shop.district_id'] = $arr_key['d'];
                $title = model('Region')->getRegionName($where['fl_shop.district_id']);
            }
            
            //判断是否有店铺
            if(isset($arr_key['s']) && !empty($arr_key['s']))
            {
                $where['fl_goods.shop_id'] = $arr_key['s'];
                $title = $title.model('Shop')->getDb()->where(['id'=>$where['fl_goods.shop_id']])->value('company_name');
            }
            
            //商品类目
            if(isset($arr_key['f']) && !empty($arr_key['f']))
            {
                $where['fl_goods.category_id'] = $arr_key['f'];
                $title = $title.model('Category')->getDb()->where(['id'=>$where['fl_goods.category_id']])->value('name');
            }
        }
        
        $where['fl_goods.delete_time'] = 0;
        $where['fl_goods.status'] = 0;
        $list = $this->getLogic()->getJoinPaginate($where, 'fl_goods.id desc', 'fl_goods.*', 15);
        if(!$list){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
        
        $page = $list->render();
        $page = preg_replace('/key=[a-z0-9]+&amp;/', '', $page);
        $page = preg_replace('/&amp;key=[a-z0-9]+/', '', $page);
        $page = preg_replace('/\?page=1"/', '"', $page);
        $this->assign('page', $page);
        $this->assign('list', $list);
        
        //seo标题设置
        $keyword = $title;
        if(isset($where['fl_goods.shop_id'])){$title = $title.'批发货源';$keyword = $title;}else{$keyword = $title.'批发市场';$title = $title.'批发市场_'.$title.'批发';}
        $this->assign('keyword',$keyword);
        $this->assign('title',$title);
        
        //相关推荐
        if(isset($province_id))
        {
            $region_list = logic('Region')->getAll(['parent_id'=>$province_id]);
            if($region_list)
            {
                foreach($region_list as $k=>$v)
                {
                    $where9['fl_shop.city_id'] = $v['id'];
                    if(isset($arr_key['f'])){$where9['fl_goods.category_id'] = $arr_key['f'];}
                    
                    $count = Db::table('fl_goods')->join('fl_shop','fl_goods.shop_id = fl_shop.id')->where($where9)->count();
                    if($count < 5)
                    {
                        unset($region_list[$k]);
                    }
                }
            }
            
            $this->assign('region_list',$region_list);
        }
        
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
    
    //详情
    public function detail()
	{
        if(!checkIsNumber(input('id',null))){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
        $id = input('id');
        
        $where['id'] = $id;
        $post = $this->getLogic()->getOne($where);
        if(!$post){$this->error('您访问的页面不存在或已被删除', '/' , 3);}
        
        $post['body']=ReplaceKeyword($post['body']);
        //var_dump($post);exit;
        $this->assign('post',$post);
        
        //店铺最新动态
        $where2['delete_time'] = 0;
        $where2['shop_id'] = $post['shop_id'];
        $where2['status'] = 0;
        $shop_posts = logic('goods')->getAll($where2, 'id desc', ['content'], 5);
        $this->assign('shop_posts',$shop_posts);
        
        //平台最新动态
        $where3['delete_time'] = 0;
        $where3['status'] = 0;
        $pingtai_posts = logic('goods')->getAll($where3, 'id desc', ['content'], 5);
        $this->assign('pingtai_posts',$pingtai_posts);
        
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
}