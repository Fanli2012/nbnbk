<?php
/**
 * 公共控制器统一继承
 */

namespace app\common\controller;

use think\Controller;

class CommonController extends Controller
{
    /**
     * 初始化
     * @param void
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
    }
}