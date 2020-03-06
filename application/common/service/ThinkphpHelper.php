<?php
/**
 * Thinkphp专用常用方法
 */

namespace app\common\service;

use think\Db;

class ThinkphpHelper
{
    public function __construct()
    {

    }

    /**
     * 获取数据库字段注释
     *
     * @param string $table_name 数据表名称(必须，不含前缀)
     * @param string $field 字段名称(默认获取全部字段,单个字段请输入字段名称)
     * @param string $table_schema 数据库名称(可选)
     * @return string
     */
    public function get_db_column_comment($table_name = '', $field = true, $table_schema = '')
    {
        // 接收参数
        $database = config('database');
        $table_schema = empty($table_schema) ? $database['database'] : $table_schema;
        $table_name = $database['prefix'] . $table_name;

        // 缓存名称
        $fieldName = $field === true ? 'allField' : $field;
        $cacheKeyName = 'db_' . $table_schema . '_' . $table_name . '_' . $fieldName;

        // 处理参数
        $param = [
            $table_name,
            $table_schema
        ];

        // 字段
        $columeName = '';
        if ($field !== true) {
            $param[] = $field;
            $columeName = "AND COLUMN_NAME = ?";
        }

        // 查询结果
        $result = Db::query("SELECT COLUMN_NAME as field,column_comment as comment FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ? AND table_schema = ? $columeName", $param);
        // pp(Db::getlastsql());
        if (empty($result) && $field !== true) {
            return $table_name . '表' . $field . '字段不存在';
        }

        // 处理结果
        foreach ($result as $k => $v) {
            $data[$v['field']] = $v['comment'];
        }

        // 字段注释格式不正确
        if (empty($data)) {
            return $table_name . '表' . $field . '字段注释格式不正确';
        }

        return count($data) == 1 ? reset($data) : $data;
    }
}