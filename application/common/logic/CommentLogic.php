<?php

namespace app\common\logic;

use think\Loader;
use think\Db;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\model\Comment;
use app\common\model\Order;

class CommentLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Comment();
    }

    public function getValidate()
    {
        return Loader::validate('Comment');
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = '', $limit = '')
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
				$res['list'][$k] = $res['list'][$k]->append(['user'])->toArray();
            }
        }

        return $res;
    }

    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);

        $res = $res->each(function ($item, $key) {
            $item = $item->append(['user'])->toArray();
            return $item;
        });

        return $res;
    }

    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);

        if ($res) {
            foreach($res as $k=>$v) {
				//评论的用户
				$res[$k]['user'] = model('User')->getOne(array('id' => $res[$k]['user_id']), 'id,nickname,user_name,head_img,sex,status,add_time');
				if (!empty($res[$k]['user']) && empty($res[$k]['user']['nickname'])) {
					$res[$k]['user']['nickname'] = $res[$k]['user']['user_name'];
				}
				//父级评论
				$res[$k]['parent_comment'] = array();
				if ($res[$k]['parent_id'] > 0) {
					$res[$k]['parent_comment'] = $this->getModel()->getOne(array('id' => $res[$k]['parent_id']), $field);
					$res[$k]['user'] = array();
					if ($res[$k]['parent_comment']) {
						$res[$k]['parent_comment']['user'] = model('User')->getOne(array('id' => $res[$k]['parent_comment']['user_id']), 'id,nickname,user_name,head_img,sex,status,add_time');
						if (!empty($res[$k]['parent_comment']['user']) && empty($res[$k]['parent_comment']['user']['nickname'])) {
							$res[$k]['parent_comment']['user']['nickname'] = $res[$k]['parent_comment']['user']['user_name'];
						}
					}
				}
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

		return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //添加时间
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }

        $check = $this->getValidate()->scene('add')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $record = $this->getModel()->getOne(array('comment_type' => $data['comment_type'], 'id_value' => $data['id_value'], 'user_id' => $data['user_id']));
        if ($record) {
            return ReturnData::create(ReturnData::FAIL, null, '您已经评价过了');
        }

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res, '评价成功');
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
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

    /**
     * 评价-批量添加
     * @return array
     */
    public function batchAddGoodsComment($data)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        $order_id = 0;
        Db::startTrans();

        foreach ($data as $k => $v) {
            $res = $this->add($v);
            $order_id = $v['order_id'];
            if ($res['code'] != ReturnData::SUCCESS) {
                Db::rollback();
                return $res;
            }
            //商品评论数增加
            model('Goods')->setIncrement(array('id' => $v['id_value']), 'comment_number', 1);
        }

        //设为已评价
        $order_data['is_comment'] = Order::ORDER_IS_COMMENT;
        $order_data['update_time'] = time();
        if (!model('Order')->edit($order_data, array('id' => $order_id))) {
            Db::rollback();
            return ReturnData::create(ReturnData::FAIL);
        }

        Db::commit();
        return ReturnData::create(ReturnData::SUCCESS, null, '评价成功');
    }

    // 添加通用评论
    public function addCommonComment($data = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        //添加时间
        if (!(isset($data['add_time']) && !empty($data['add_time']))) {
            $data['add_time'] = time();
        }

		$data['ip_address'] = Helper::getRemoteIp();

		//评论内容最多240个字符
		if (isset($data['content']) && !empty($data['content'])) {
			$data['content'] = mb_strcut($data['content'], 0, 240, 'UTF-8');
			$data['content'] = trim($data['content']);
        }

        $check = $this->getValidate()->scene('add_common_comment')->check($data);
        if (!$check) {
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $this->getValidate()->getError());
        }

        $res = $this->getModel()->add($data);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

}