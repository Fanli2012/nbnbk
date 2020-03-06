<?php

namespace app\common\logic;

use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\Goods;

class GoodsLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Goods();
    }

    public function getValidate()
    {
        return Loader::validate('Goods');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['count'] > 0) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
                //$res['list'][$k]['typename'] = $this->getModel()->getTypenameAttr($v);
                $res['list'][$k] = $res['list'][$k]->append(['price', 'is_promote', 'goods_img_list', 'type_name_text', 'status_text'])->toArray();
            }
        }

        return $res;
    }

    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);

        $res = $res->each(function ($item, $key) {
            //$item = $this->getDataView($item);
            return $item;
        });

        return $res;
    }

    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);

        /* if($res)
        {
            foreach($res as $k=>$v)
            {
                //$res[$k] = $this->getDataView($v);
            }
        } */

        return $res;
    }

    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if (!$res) {
            return false;
        }

        $res = $res->append(['price', 'is_promote', 'goods_img_list', 'type_name_text', 'status_text'])->toArray();

        //$res = $this->getDataView($res);
        //$res['typename'] = $this->getModel()->getTypenameAttr($res);

        $this->getModel()->getDb()->where($where)->setInc('click', 1);

        return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

		//标题最多150个字符
		if (isset($data['title']) && !empty($data['title'])) {
			$data['title'] = mb_strcut($data['title'],0,150,'UTF-8');
			$data['title'] = trim($data['title']);
        }
		//SEO标题最多150个字符
		if (isset($data['seotitle']) && !empty($data['seotitle'])) {
			$data['seotitle'] = mb_strcut($data['seotitle'],0,150,'UTF-8');
			$data['seotitle'] = trim($data['seotitle']);
        }
		//关键词最多60个字符
		if (isset($data['keywords']) && !empty($data['keywords'])) {
			$data['keywords'] = mb_strcut($data['keywords'],0,60,'UTF-8');
			$data['keywords'] = trim($data['keywords']);
        }
		//描述最多240个字符
		if (isset($data['description']) && !empty($data['description'])) {
			$data['description'] = mb_strcut($data['description'],0,240,'UTF-8');
			$data['description'] = trim($data['description']);
        }
        //添加时间、更新时间
		$time = time();
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = $time;
        }
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = $time;
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        //判断货号
        if (isset($data['sn']) && !empty($data['sn'])) {
            $where_sn['sn'] = $data['sn'];
            if ($this->getModel()->getOne($where_sn)) {
                return ReturnData::create(ReturnData::FAIL, null, '该货号已存在');
            }
        }

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

		//标题最多150个字符
		if (isset($data['title']) && !empty($data['title'])) {
			$data['title'] = mb_strcut($data['title'],0,150,'UTF-8');
			$data['title'] = trim($data['title']);
        }
		//SEO标题最多150个字符
		if (isset($data['seotitle']) && !empty($data['seotitle'])) {
			$data['seotitle'] = mb_strcut($data['seotitle'],0,150,'UTF-8');
			$data['seotitle'] = trim($data['seotitle']);
        }
		//关键词最多60个字符
		if (isset($data['keywords']) && !empty($data['keywords'])) {
			$data['keywords'] = mb_strcut($data['keywords'],0,60,'UTF-8');
			$data['keywords'] = trim($data['keywords']);
        }
		//描述最多240个字符
		if (isset($data['description']) && !empty($data['description'])) {
			$data['description'] = mb_strcut($data['description'],0,240,'UTF-8');
			$data['description'] = trim($data['description']);
        }
        //更新时间
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = time();
        }

        $check = $this->getValidate()->scene('edit')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        //判断货号
        if (isset($data['sn']) && !empty($data['sn'])) {
            $where_sn['sn'] = $data['sn'];
            $where_sn['id'] = ['<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where_sn)) {
                return ReturnData::create(ReturnData::FAIL, null, '该货号已存在');
            }
        }

        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //删除
    public function del($where)
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $check = $this->getValidate()->scene('del')->check($where);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $res = $this->getModel()->edit(array('delete_time' => time()), $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = array())
    {
        return getDataAttr($this->getModel(), $data);
    }

    /**
     * 递归获取面包屑导航
     * @param  [int] $type_id
     * @return [string]
     */
    public function get_goods_type_path($type_id)
    {
        global $temp;

        $row = model('GoodsType')->getOne(['id' => $type_id], 'name,parent_id,id');

        $temp = '<a href="/goodslist/f' . $row["id"] . '">' . $row["name"] . "</a> > " . $temp;

        if ($row['parent_id'] > 0) {
            $this->get_goods_type_path($row['parent_id']);
        }

        return $temp;
    }

}