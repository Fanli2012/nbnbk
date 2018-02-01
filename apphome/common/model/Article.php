<?php
namespace app\common\model;

use think\Db;

class Article extends Base
{
    // 模型会自动对应数据表，模型类的命名规则是除去表前缀的数据表名称，采用驼峰法命名，并且首字母大写，例如：模型名UserType，约定对应数据表think_user_type(假设数据库的前缀定义是 think_)
    // 设置当前模型对应的完整数据表名称
    //protected $table = 'fl_article';
    
    // 默认主键为自动识别，如果需要指定，可以设置属性
    protected $pk = 'id'; 
    
    // 设置当前模型的数据库连接
    /* protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => '127.0.0.1',
        // 数据库名
        'database'    => 'thinkphp',
        // 数据库用户名
        'username'    => 'root',
        // 数据库密码
        'password'    => '123456',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => 'fl_',
        // 数据库调试模式
        'debug'       => false,
    ]; */
    
    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 15)
    {
        $res['count'] = self::where($where)->count();
        $res['list'] = array();
        
        if($res['count'] > 0)
        {
            $res['list'] = self::where($where)->field($field)->order($order)->limit($offset.','.$limit)->select();
        }
        
        return $res;
    }
    
    //分页，用于前端html输出
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 15)
    {
        return self::where($where)->field($field)->order($order)->paginate($limit, false, ['query' => request()->param()]);
    }
    
    //获取一条
    public function getOne($where, $field = '*')
    {
        return self::where($where)->field($field)->find();
    }
    
    //添加
    public function add($data)
    {
        // 过滤数组中的非数据表字段数据
        //return $this->allowField(true)->isUpdate(false)->save($data);
        
        // 添加单条数据
        return db('article')->insert($data);
        
        // 添加多条数据
        //db('article')->insertAll($list);
    }
    
    //修改
    public function modify($data, $where = array())
    {
        return $this->allowField(true)->isUpdate(true)->save($data, $where);
    }
    
    //删除
    public function remove($where)
    {
        return $this->where($where)->delete();
    }
    
    //是否审核
    public function getIscheckAttr($data)
    {
        $arr = array[0 => '已审核', 1 => '未审核',];
        return $arr[$data['ischeck']];
    }
    
    //是否栏目名称
    public function getTypenameAttr($data)
    {
        return db('arctype')->where(['id'=>$data['typeid']])->value('typename');
    }
}