<?php

namespace app\fladmin\controller;

use think\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ArticleTypeLogic;

class ArticleType extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new ArticleTypeLogic();
    }

    //列表
    public function index()
    {
        $list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $this->assign('list', $list);

        return $this->fetch();
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            //表单令牌验证
            $validate = new Validate([
                ['__token__', 'require|token', '非法提交|请不要重复提交表单'],
            ]);
            if (!$validate->check($_POST)) {
                $this->error($validate->getError());
            }

            $_POST['add_time'] = $_POST['update_time'] = time(); //添加时间、更新时间
			$_POST['admin_id'] = $this->admin_info['id']; // 管理员发布者ID
			
            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        $parent_id = input('parent_id', 0);
        if ($parent_id != 0) {
            if (preg_match('/[0-9]*/', $parent_id)) {
            } else {
                $this->error('参数错误');
            }
            $this->assign('parent_article_type', model('ArticleType')->getOne(['id' => $parent_id], ['content']));
        }
        $this->assign('parent_id', $parent_id);

        return $this->fetch();
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $_POST['update_time'] = time(); //更新时间

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
        if (!checkIsNumber(input('id', null))) {
            $this->error('删除失败！请重新提交');
        }
        $id = $where['id'] = input('id');

        if ($this->getLogic()->getOne(['parent_id' => $where['id']])) {
            $this->error('删除失败！请先删除子栏目');
        }

        $res = $this->getLogic()->del($where);
        if ($res['code'] != ReturnData::SUCCESS) {
            $this->error($res['msg']);
        }

        if (model('Article')->getCount(['type_id' => $id]) > 0) //判断该分类下是否有文章，如果有把该分类下的文章也一起删除
        {
            if (model('Article')->del(['type_id' => $id])) {
                $this->success('删除成功', url('index'), '', 1);
            }

            $this->error('栏目下的文章删除失败');
        }

        $this->success('删除成功', url('index'), '', 1);
    }

}