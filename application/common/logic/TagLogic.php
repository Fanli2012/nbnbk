<?php

namespace app\common\logic;

use think\Loader;
use app\common\lib\ReturnData;
use app\common\model\Tag;

class TagLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Tag();
    }

    public function getValidate()
    {
        return Loader::validate('Tag');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
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

        if ($res) {
            foreach ($res as $k => $v) {
                //$res[$k] = $this->getDataView($v);
            }
        }

        return $res;
    }

    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if (!$res) {
            return false;
        }

        //$res = $this->getDataView($res);

        return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //添加时间、更新时间
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }
        if (!(isset($data['update_time']) && !empty($data['update_time']))) {
            $data['update_time'] = time();
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        //判断名称
        if (isset($data['name']) && !empty($data['name'])) {
            if ($this->getModel()->getOne(['name' => $data['name']])) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '名称已经存在');
            }
        }

        //判断别名
        if (isset($data['filename']) && !empty($data['filename'])) {
            if ($this->getModel()->getOne(['filename' => $data['filename']])) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '别名已经存在');
            }
        }

        if (isset($data["keywords"])) {
            $data['keywords'] = str_replace("，", ",", $data["keywords"]);
        } else {
            unset($data['keywords']);
        } //关键词

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

        // 更新时间
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

        //判断名称
        if (isset($data['name']) && !empty($data['name'])) {
            $where2['name'] = $data['name'];
            $where2['id'] = ['<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where2)) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '名称已经存在');
            }
        }

        //判断别名
        if (isset($data['filename']) && !empty($data['filename'])) {
            $where2['filename'] = $data['filename'];
            $where2['id'] = ['<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where2)) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '别名已经存在');
            }
        }

        if (isset($data["keywords"])) {
            $data['keywords'] = str_replace("，", ",", $data["keywords"]);
        } else {
            unset($data['keywords']);
        } //关键词

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

        $res = $this->getModel()->del($where);
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

}