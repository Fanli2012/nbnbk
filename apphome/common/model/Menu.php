<?php
namespace app\common\model;

class Menu extends Base
{
	// 设置当前模型对应的完整数据表名称
    protected $table = 'menu';
    // 默认主键为自动识别，如果需要指定，可以设置属性
    protected $pk = 'id';
    
    public function getDb()
    {
        return db('menu');
    }
    
    /**
     * 列表
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $offset 偏移量
     * @param int $limit 取多少条
     * @return array
     */
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 15)
    {
        $res['count'] = $this->getDb()->where($where)->count();
        $res['list'] = array();
        
        if($res['count'] > 0)
        {
            $res['list'] = $this->getDb()->where($where);
            
            if(is_array($field))
            {
                $res['list'] = $res['list']->field($field[0],true);
            }
            else
            {
                $res['list'] = $res['list']->field($field);
            }
            
            $res['list'] = $res['list']->order($order)->limit($offset.','.$limit)->select();
        }
        
        return $res;
    }
    
    /**
     * 分页，用于前端html输出
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 每页几条
     * @param int|bool $simple 是否简洁模式或者总记录数
     * @param int $page 当前第几页
     * @return array
     */
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 15, $simple = false)
    {
        $res = $this->getDb()->where($where);
        
        if(is_array($field))
        {
            $res = $res->field($field[0],true);
        }
        else
        {
            $res = $res->field($field);
        }
        
        return $res->order($order)->paginate($limit, $simple, array('query' => request()->param()));
    }
    
    /**
     * 查询全部
     * @param array $where 查询条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 取多少条
     * @return array
     */
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getDb()->where($where);
            
        if(is_array($field))
        {
            $res = $res->field($field[0],true);
        }
        else
        {
            $res = $res->field($field);
        }
        
        $res = $res->order($order)->limit($limit)->select();
        
        return $res;
    }
    
    /**
     * 获取一条
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getOne($where, $field = '*')
    {
        $res = $this->getDb()->where($where);
        
        if(is_array($field))
        {
            $res = $res->field($field[0],true);
        }
        else
        {
            $res = $res->field($field);
        }
        
        $res = $res->find();
        
        return $res;
    }
    
    /**
     * 添加
     * @param array $data 数据
     * @return int
     */
    public function add($data,$type=0)
    {
        // 过滤数组中的非数据表字段数据
        // return $this->allowField(true)->isUpdate(false)->save($data);
        
        if($type==0)
        {
            // 新增单条数据并返回主键值
            return $this->getDb()->strict(false)->insertGetId($data);
        }
        elseif($type==1)
        {
            // 添加单条数据
            return $this->getDb()->strict(false)->insert($data);
        }
        elseif($type==2)
        {
            /**
             * 添加多条数据
             * $data = [
             *     ['foo' => 'bar', 'bar' => 'foo'],
             *     ['foo' => 'bar1', 'bar' => 'foo1'],
             *     ['foo' => 'bar2', 'bar' => 'foo2']
             * ];
             */
            
            return $this->getDb()->strict(false)->insertAll($data);
        }
    }
    
    /**
     * 修改
     * @param array $data 数据
     * @param array $where 条件
     * @return bool
     */
    public function edit($data, $where = array())
    {
        return $this->getDb()->strict(false)->where($where)->update($data);
    }
    
    /**
     * 删除
     * @param array $where 条件
     * @return bool
     */
    public function del($where)
    {
        return $this->getDb()->where($where)->delete();
    }
    
    /**
     * 统计数量
     * @param array $where 条件
     * @param string $field 字段
     * @return int
     */
    public function getCount($where, $field = '*')
    {
        return $this->getDb()->where($where)->count($field);
    }
    
    /**
     * 获取最大值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getMax($where, $field)
    {
        return $this->getDb()->where($where)->max($field);
    }
    
    /**
     * 获取最小值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getMin($where, $field)
    {
        return $this->getDb()->where($where)->min($field);
    }
    
    /**
     * 获取平均值
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getAvg($where, $field)
    {
        return $this->getDb()->where($where)->avg($field);
    }
    
    /**
     * 统计总和
     * @param array $where 条件
     * @param string $field 要统计的字段名（必须）
     * @return null
     */
    public function getSum($where, $field)
    {
        return $this->getDb()->where($where)->sum($field);
    }
    
    /**
     * 查询某一字段的值
     * @param array $where 条件
     * @param string $field 字段
     * @return null
     */
    public function getValue($where, $field)
    {
        return $this->getDb()->where($where)->value($field);
    }
    
    /**
     * 查询某一列的值
     * @param array $where 条件
     * @param string $field 字段
     * @return array
     */
    public function getColumn($where, $field)
    {
        return $this->getDb()->where($where)->column($field);
    }
    
	//将栏目列表生成数组
	public function get_category($modelname,$parent_id=0,$pad=0)
	{
		$arr=array();
		
		$cats = db($modelname)->where("parent_id=$parent_id")->order('id asc')->select();
		
		if($cats)
		{
			foreach($cats as $row)//循环数组
			{
				$row['deep'] = $pad;
				if($child = $this->get_category($modelname,$row["id"],$pad+1))//如果子级不为空
				{
					$row['child'] = $child;
				}
				$arr[] = $row;
			}
			return $arr;
		}
	}

	public function category_tree($list,$parent_id=0)
	{
		global $temp;
		if(!empty($list))
		{
			foreach($list as $v)
			{
				$temp[] = array("id"=>$v['id'],"deep"=>$v['deep'],"name"=>$v['name'],"parent_id"=>$v['parent_id']);
				//echo $v['id'];
				if(array_key_exists("child",$v))
				{
					$this->category_tree($v['child'],$v['parent_id']);
				}
			}
		}
		
		return $temp;
	}
	
	//获取后台管理员所具有权限的菜单列表
	public function getPermissionsMenu($role_id, $parent_id=0, $pad=0)
	{
		$res = array();
		
		$where['fl_access.role_id'] = $role_id;
		$where['fl_menu.parent_id'] = $parent_id;
		$where["fl_menu.status"] = 1;
		
		$menu =db('menu')
			->join('fl_access', 'fl_access.menu_id = fl_menu.id')
            ->field('fl_menu.*, fl_access.role_id')
			->where($where)
			->order('fl_menu.listorder asc')
            ->select();
		
		if($menu)
		{
			foreach($menu as $row)
			{
				$row['deep'] = $pad;
				
				if($PermissionsMenu = $this->getPermissionsMenu($role_id, $row['id'], $pad+1))
				{
					$row['child'] = $PermissionsMenu;
				}
				
				$res[] = $row;
			}
		}
		
		return $res;
	}
}