<?php

namespace app\common\lib;

/**
 * 加密算法类
 */
class Encrypt
{
    public function __construct()
    {

    }

    /**
     * md5()加密(单项加密,无法解密)
     * 单项散列加密
     * @param string $str 需要加密的内容
     * @param int $number 加密次数
     * @param bool $raw_output true，16字节长度的原始二进制格式返回。false，返回32位字符十六进制数字形式返回散列值。
     * @return string
     */
    public function md5Encrypt($str, $number = 1, $raw_output = false)
    {
        $res = '';
        for ($i = 1; $i <= $number; $i++) {
            $res = empty($res) ? md5($str, $raw_output) : md5($res, $raw_output);
        }
        return $res;
    }

    /**
     * crypt()加密(单项加密,无法解密)
     * 单项散列加密
     * @param string $str 需要加密的内容
     * @param string $salt 干扰串 如果没有$salt，将随机生成一个干扰串，否则刷新加密密文不变。
     * @return string 返回一个基于UNIX DES算法或系统上其他可用的替代算法的散列字符串
     */
    public function cryptEncrypt($str, $salt)
    {
        $res = crypt($str, $salt);
        return $res;
    }

    /**
     * sha1加密(单向加密，无法解密)
     * 单项散列加密
     * @param string $str 需要加密的内容
     * @param bool $raw_output true，以20字符长度的原始格式返回。false,返回值是一个40字符长度的十六进制数字。
     * @return string
     */
    public function sha1Encrypt($str, $raw_output = false)
    {
        $res = sha1($str, $raw_output);
        return $res;
    }

    /**
     * 编码url字符串
     * URL编码加密（双向加密，可以解密）
     * @param string $str 需要加密的内容
     * @return string
     */
    public function urlEncodeEncrypt($str)
    {
        $res = urlencode($str);
        return $res;
    }

    /**
     * 解码已编码的url字符串
     * URL编码加密（双向加密，可以解密）
     * @param string $str 需要加密的内容
     * @return string
     */
    public function urlDecodeEncrypt($str)
    {
        $res = urldecode($str);
        return $res;
    }

    /**
     * 使用base64对data进行编码
     * base64编码加密(双向加密，可以解密)
     * @param string $str 需要加密的内容
     * @return string
     */
    public function base64EncodeEncrypt($str)
    {
        $res = base64_encode($str);
        return $res;
    }

    /**
     * 对使用MIME base64编码的数据进行解码
     * base64编码加密(双向加密，可以解密)
     * @param string $str 需要加密的内容
     * @param bool $strict 如果输入的数据超除了base64字母表，则返回false
     * @return string
     */
    public function base64DecodeEncrypt($str, $strict = false)
    {
        $res = base64_decode($str, $strict);
        return $res;
    }
}