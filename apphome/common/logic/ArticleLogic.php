<?php
namespace app\common\logic;

use app\common\model\Article;
use think\Loader;

class ArticleLogic extends BaseLogic
{
    public $_model;
    public $_validate;
    
    protected function initialize()
    {
        parent::initialize();
        
        $this->_model = new Article();
        $this->_validate = Loader::validate('Article');
    }
    
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 15)
    {
        $rs = $this->_model->getList($where, $order, $field, $offset, $limit);
        
        return $rs;
    }
    
    public function getOne($data = [])
    {
        extract($data);
        
        if (empty($data)){return false;}
        
        $res = $this->_model->getOne($data);
        
        return $res;
    }
    
    public function add($data = [])
    {
        if (empty($data))
        {
            return false;
        }
        
        $rs = $this->_validate->scene('add')->check($data);
        if ($rs !== false)
        {
            $data['addtime'] = $data['pubdate'] = time();
            return ReturnData::create(ReturnData::SUCCESS,$this->_model->add($data));
        }
        else
        {
            return ReturnData::create(ReturnData::PARAMS_ERROR,null,$this->_validate->getError());
        }
    }
    
    public function edit($data,$where)
    {
        extract($data);
        
        if (empty($data)){return ReturnData::create(ReturnData::SUCCESS);}
        
        return $this->_model->modify($data,$where);
    }
    
    public function del($data)
    {
        extract($data);
        
        $where = '';
        if(isset($car_id)){$where['car_id'] = $car_id;}
        
        if($this->_model->remove($where))
        {
            return ReturnData::create(ReturnData::SUCCESS);
        }
        else
        {
            return ReturnData::create(ReturnData::SYSTEM_FAIL);
        }
    }
    
    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = [])
    {
        return getDataAttr($this->_model,$data);
    }
}