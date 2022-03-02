<?php

namespace app\fladmin\controller;

use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\UserLogic;
use app\common\model\User as UserModel;

class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new UserLogic();
    }

    //列表
    public function index()
    {
        $where = array();
		//起止时间
        if (!empty($_REQUEST['start_date']) && !empty($_REQUEST['end_date'])) {
            $start_date = strtotime(date($_REQUEST['start_date']));
			$end_date = strtotime(date($_REQUEST['end_date']));
			if ($start_date > $end_date) {
				$this->error('起止时间不正确');
			}
			
			$end_date = $end_date + 24 * 3600;
			$where['add_time'] = [['>=',$start_date],['<=', $end_date]];
        }
        //是否在线
        if (isset($_REQUEST['is_online']) && $_REQUEST['is_online'] == 1) {
            $where['login_time'] = ['>', (time() - 300)];
        }
        if (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') {
            $where['mobile|true_name|nickname'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }
        //用户ID
        if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
            $where['id'] = $_REQUEST['id'];
        }
        //推荐人ID
        if (isset($_REQUEST['parent_id']) && $_REQUEST['parent_id'] > 0) {
            $where['parent_id'] = $_REQUEST['parent_id'];
        }
        //用户状态
        if (isset($_REQUEST['status']) && $_REQUEST['status'] != '') {
            $where['status'] = $_REQUEST['status'];
        }
        //用户等级
        if (isset($_REQUEST['user_rank']) && $_REQUEST['user_rank'] != '') {
            $where['user_rank'] = $_REQUEST['user_rank'];
        }
        //登录终端
        if (isset($_REQUEST['user_agent']) && $_REQUEST['user_agent'] != '') {
            $where['user_agent'] = $_REQUEST['user_agent'];
        }
        $list = $this->getLogic()->getPaginate($where, 'id desc');

        $this->assign('page', $list->render());
        $this->assign('list', $list);
        //echo '<pre>';print_r($list);exit;
        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->register($_POST);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error($res['msg']);
            }

            $this->success('操作成功', url('index'), '', 1);
        }

        $assign_data['user_rank'] = model('UserRank')->getAll(array(), 'rank asc');
        $this->assign($assign_data);
        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $res = $this->getLogic()->userInfoUpdate($_POST, $where);
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
        if (!checkIsNumber(input('id', null))) {
            $this->error('删除失败！请重新提交');
        }
        $where['id'] = input('id');

        $res = $this->getLogic()->del($where);
        if ($res['code'] == ReturnData::SUCCESS) {
            $this->success('删除成功');
        }

        $this->error($res['msg']);
    }

    //锁定
    public function lock()
    {
        $id = input('id', '');
        if (!$id) {
            $this->error('参数错误');
        }
        if (is_numeric($id)) {
            $where['id'] = input('id');
        } else {
			$where = "id in ($id)";
		}
        $data['status'] = 2;
        $res = model('User')->edit($data, $where);
        if ($res) {
            $this->success("$id ,锁定成功");
        }

        $this->error("$id ,锁定失败！请重新提交");
    }

    //解锁
    public function unlock()
    {
        $id = input('id', '');
        if (!is_numeric($id)) {
			$this->error('参数错误');
        }
		$where['id'] = $id;
        $data['status'] = 0;
        $data['update_time'] = time();
        $res = model('User')->edit($data, $where);
        if (!$res) {
			$this->error("$id ,解锁失败！请重新提交");
        }

		model('Log')->del(['login_id'=>$id, 'type'=>7, 'http_method'=>'POST']);
        $this->success("$id ,解锁成功");
    }

    //重置密码或支付密码
    public function reset_pwd()
    {
        $id = input('id', '');
        if (!is_numeric($id)) {
			$this->error('参数错误');
        }
		$where['id'] = $id;
		if (input('type', 1) == 1) {
			$data['password'] = 'e10adc3949ba59abbe56e057f20f883e';
			$data['password2'] = '123456';
		} else {
			$data['pay_password'] = 'e10adc3949ba59abbe56e057f20f883e';
			$data['pay_password2'] = '123456';
		}
        $res = model('User')->edit($data, $where);
        if (!$res) {
			$this->error("$id ,操作失败！请重新提交");
        }
        $this->success("$id ,操作成功");
    }

	//签到人数
    public function signin_num()
    {
		//当天签到人数
		$time = strtotime(date('Y-m-d')); //今天日期时间戳
		$where = array('signin_time' => [['>=',$time],['<', ($time + 3600 * 24)]]);
		$list = $this->getLogic()->getPaginate($where, 'signin_time desc', 'id,mobile,parent_id,head_img,true_name,idcard,signin_time');

        $this->assign('page', $list->render());
        $this->assign('list', $list);
		
		return $this->fetch();
    }

    //导出Excel
    public function output_excel()
    {
        $res = '';
        $where = array();
        //导出Excel
        $excel_title = array('ID', '手机号', '注册时间');
        $cellData = array();
        array_push($cellData, $excel_title);
        $order_list = model('User')->getAll($where, 'id desc', 'id,mobile,add_time', 1000);
        if ($order_list) {
            foreach ($order_list as $k => $v) {
                array_push($cellData, array($v['id'], $v['mobile'], date('Y-m-d H:i:s', $v['add_time'])));
            }
        }
        $excel_data = $cellData;
        logic('Excel')->export_excel($excel_title, $excel_data, '用户1000条', './', true);
    }

}