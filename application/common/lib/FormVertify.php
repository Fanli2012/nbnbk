<?php

namespace app\common\lib;

/**
 * 表单验证通用类
 */
class FormVertify
{

    public function __construct()
    {

    }

    /**
     * php较低版本（php version <= 5.3.0）数据处理
     * @param $data
     * @return string
     */
    public function lowVersion($data)
    {
        $data = trim($data);//去除空值
        $data = stripslashes($data);//去除反斜杠
        $data = htmlspecialchars($data);//转义
        return $data;
    }

    /**
     *  php高版本（php version >= 6.0）数据处理
     * @param $data
     * @return string
     */
    public function highVersion($data)
    {
        $data = trim($data);//去除空值
        $data = addslashes($data);//添加反斜杠
        $data = htmlspecialchars($data);//转义
        return $data;
    }

    /**
     * php防注入安全过滤函数
     * @param $data
     * @return mixed|string
     */
    public function checkInput($data)
    {
        $data = addslashes($data);//对特殊符号添加反斜杠
        if (get_magic_quotes_gpc()) {//判断自动添加反斜杠是否开启
            $data = stripslashes($data);//去除反斜杠
        }
        $data = str_replace("_", "\_", $data);//把'_'过滤掉
        $data = str_replace("%", "\%", $data);//把'%'过滤掉
        $data = str_replace("*", "\*", $data);//把'*'过滤掉
        $data = nl2br($data);//回车转换
        $data = trim($data);//去掉前后空格
        $data = htmlspecialchars($data);//将HTML特殊字符转化为实体
        return $data;
    }

    /**
     * 验证手机号码合法性
     * @param string $mobile 手机号码
     * @return array
     */
    public function checkMobile($mobile)
    {
        $pattern = '/^((\(\d{3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}$/';
        if (!preg_match($pattern, $mobile)) {
            $result = ['code' => 0, 'msg' => "非法手机格式", 'data' => ''];
        } else {
            $result = ['code' => 1, 'msg' => "手机格式正确", 'data' => ''];
        }
        return $result;
    }

    /**
     * 验证身份证号码合法性
     * @param string $str 身份证
     * @return array
     */
    public function checkIdCard($str)
    {
        $pattern = '/(^([\d]{15}|[\d]{18}|[\d]{17}x)$)/';
        if (!preg_match($pattern, $str)) {
            $result = ['code' => 0, 'msg' => "非法身份证格式", 'data' => ''];
        } else {
            $result = ['code' => 1, 'msg' => "身份证格式正确", 'data' => ''];
        }
        return $result;
    }

    /**
     * 验证e-mail邮箱合法性
     * @param string $email e-mail
     * @return array
     */
    public function checkEmail($email)
    {
        $pattern = '/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/';
        if (!preg_match($pattern, $email)) {
            $result = ['code' => 0, 'msg' => "非法邮箱格式", 'data' => ''];
        } else {
            $result = ['code' => 1, 'msg' => "邮箱格式正确", 'data' => ''];
        }
        return $result;
    }

    /**
     * 验证邮编格式合法性
     * @param string $str e-mail
     * @return array
     */
    public function checkZip($str)
    {
        $pattern = '/^[1-9]\d{5}$/';
        if (!preg_match($pattern, $str)) {
            $result = ['code' => 0, 'msg' => "非法邮编格式", 'data' => ''];
        } else {
            $result = ['code' => 1, 'msg' => "邮编格式正确", 'data' => ''];
        }
        return $result;
    }

    /**
     * 检测URL地址合法性
     * @param string $url url
     * @param int $type 1使用正则表达式验证(默认) 2使用PHP内置函数验证
     * @return array
     */
    public function checkUrl($url, $type = 1)
    {
        $result = [];
        switch ($type) {
            case 1://使用正则表达式验证
                $pattern = '/^[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?$/';
                if (!preg_match($pattern, $url)) {
                    $result = ['code' => 0, 'msg' => "非法URL地址格式", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "URL地址格式正确", 'data' => ''];
                }
                break;
            case 2://使用PHP内置函数验证
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $result = ['code' => 0, 'msg' => "非法URL地址格式", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "URL地址格式正确", 'data' => ''];
                }
                break;
        }
        return $result;
    }

    /**
     * 验证字符长度
     * @param string $num1 长度最小值
     * @param string $num2 长度最大值
     * @param string $str 字符串
     * @param int $type 1验证是否为指定长度的字母/数字组合  2验证十分位指定长度数字 3验证是否为指定长度汉字
     * @return array
     */
    public function checkStringLength($num1, $num2, $str, $type = 1)
    {
        $result = [];
        switch ($type) {
            case 1://验证是否为指定长度的字母/数字组合
                $pattern = "/^[a-zA-Z0-9]{" . $num1 . "," . $num2 . "}$/";
                if (!preg_match($pattern, $str)) {
                    $result = ['code' => 0, 'msg' => "非法字符长度格式", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "字符长度格式正确", 'data' => ''];
                }
                break;
            case 2://验证十分位指定长度数字
                $pattern = "/^[0-9]{" . $num1 . "," . $num2 . "}$/i";
                if (!preg_match($pattern, $str)) {
                    $result = ['code' => 0, 'msg' => "非法字符长度格式", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "字符长度格式正确", 'data' => ''];
                }
                break;
            case 3://验证是否为指定长度汉字
                $pattern = "/^([\x81-\xfe][\x40-\xfe]){" . $num1 . "," . $num2 . "}$/";
                if (!preg_match($pattern, $str)) {
                    $result = ['code' => 0, 'msg' => "非法字符长度格式", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "字符长度格式正确", 'data' => ''];
                }
                break;
        }
        return $result;
    }

    /**
     * 字符串匹配
     * @param string $check_str 需要验证字符
     * @param string $length_str 完整的文本
     * @param int $type 1判断指定字符串是不是在另一个字符串中 2忽略大小写匹配
     * @return array
     */
    public function checkStringMatch($check_str, $length_str, $type = 1)
    {
        $result = [];
        switch ($type) {
            case 1://判断指定字符串是不是在另一个字符串中
                $pattern = "/" . $check_str . "/";
                if (!preg_match($pattern, $length_str)) {
                    $result = ['code' => 0, 'msg' => "字符串匹配失败", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "字符串匹配成功", 'data' => ''];
                }
                break;
            case 2://忽略大小写匹配
                $pattern = "/" . $check_str . "/i";
                if (!preg_match($pattern, $length_str)) {
                    $result = ['code' => 0, 'msg' => "字符串匹配失败", 'data' => ''];
                } else {
                    $result = ['code' => 1, 'msg' => "字符串匹配成功", 'data' => ''];
                }
                break;
        }
        return $result;
    }

}