<?php

namespace app\shop\controller;

class Base extends Common
{
    /**
     * 初始化
     * @param void
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

        $route = request()->module() . '/' . request()->controller() . '/' . request()->action();

        if (!session('shop_info')) {
            $this->error('您访问的页面不存在或已被删除', '/', '', 3);
        }
    }
}
