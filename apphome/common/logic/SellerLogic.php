<?php
namespace app\common\logic;

use app\common\model\Seller;
use think\Loader;

//车行管理
class SellerLogic extends BaseLogic
{
    public $_model;
    public $_validate;
    
    protected function initialize()
    {
        parent::initialize();
        
        $this->_model = new Seller();
        $this->_validate = Loader::validate('Seller');
    }
    
    public function getList($map = [], $order = '', $field = '*', $limit = 10)
    {
        $rs = $this->_model->getList($map, $order, $field, $limit);
        
        if (!empty($rs))
        {
            $page = $rs->render();
            $list = $rs->toArray();
        }
        else
        {
            return false;
        }
        
        return ['list' => $list, 'page' => $page];
    }
    
    public function getItem($data = [])
    {
        extract($data);
        
        if (empty($data)){return false;}
        
        $res = $this->_model->getItem($data);
        
        return $res;
    }
    
    public function addItem($data = [])
    {
        if (empty($data))
        {
            return false;
        }
        
        $rs = $this->_validate->scene('add')->check($data);
        if ($rs !== false)
        {
            $data['add_time'] = time();
            return $this->_model->addItem($data);
        }
        else
        {
            $this->error($this->_validate->getError());
        }
    }
    
    public function editItem($data,$where)
    {
        extract($data);
        
        if (empty($data)){$this->success('修改成功');}
        
        return $this->_model->editItem($data,$where);
    }
    
    public function delItem($data)
    {
        extract($data);
        
        $where = '';
        if(isset($car_id)){$where['car_id'] = $car_id;}
        
        if($this->_model->delItem($where))
        {
            $this->success('删除成功');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}