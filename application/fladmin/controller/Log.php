<?php

namespace app\fladmin\controller;

use think\Db;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\LogLogic;

class Log extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new LogLogic();
    }

    //列表
    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where['login_name|ip|url|content'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        //用户ID
        if (isset($_REQUEST['login_id'])) {
			$where['login_id'] = $_REQUEST['login_id'];
        }
        //IP
        if (isset($_REQUEST['ip'])) {
			$where['ip'] = $_REQUEST['ip'];
        }
        //模块
        if (isset($_REQUEST['type']) && $_REQUEST['type'] !== '') {
			$where['type'] = $_REQUEST['type'];
        }
        //请求方式
        if (isset($_REQUEST['http_method'])) {
			$where['http_method'] = $_REQUEST['http_method'];
        }
		
		
        $list = $this->getLogic()->getPaginate($where, ['id' => 'desc']);

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        if (!checkIsNumber(input('id', null))) {
            $this->error('参数错误');
        }
        $where['id'] = input('id');
        $this->assign('id', $where['id']);

        $post = $this->getLogic()->getOne($where);
        $this->assign('post', $post);

        return $this->fetch();
    }

    //删除
    public function del()
    {
		if (empty($_GET["id"])) {
            $this->error('参数错误');
        }
		$id = $_GET["id"];
        $res = model('Log')->del("id in ($id)");
        if (!$res) {
            $this->error('删除失败');
        }

        $this->success("$id ,删除成功", url('index'), '', 1);
    }

    //清空
    public function clear()
    {
        // 截断表
        Db::execute('truncate table `fl_log`');
        $this->success('操作成功', url('index'), '', 1);
    }
}