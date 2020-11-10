<?php

namespace app\common\lib;

/**
 * SSL生成和验证类
 */
class OpenSsl
{
    private $salt;
    private $private_key;
    private $public_key;
    private $privateKeyFile;
    private $publicKeyFile;
    private $signatureFile;

    public function __construct()
    {
        $this->salt = 'demo';
        $this->privateKeyFile = 'saveSsl/private_key.pem';
        $this->publicKeyFile = 'saveSsl/public_key.pem';
        $this->signatureFile = 'saveSsl/signature.dat';

        //判断文件是否存在
        if (!file_exists($this->privateKeyFile) || !file_exists($this->publicKeyFile) || !file_exists($this->signatureFile)) {
            $res = $this->createSsl();
            if ($res['code'] != 1) {
                return $res;
            }
        }
        //获取私钥文件内容
        $handle = fopen($this->privateKeyFile, "r");
        $this->private_key = fread($handle, filesize($this->privateKeyFile));
        fclose($handle);
        //获取公钥文件内容
        $handle = fopen($this->publicKeyFile, "r");
        $this->public_key = fread($handle, filesize($this->publicKeyFile));
        fclose($handle);
    }

    /**
     * 生成SSL私钥秘钥并存储
     * @return array
     */
    public function createSsl()
    {
        //创建新的私钥和公钥
        $new_key_pair = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        ));
        openssl_pkey_export($new_key_pair, $private_key_pem);
        $details = openssl_pkey_get_details($new_key_pair);
        $public_key_pem = $details ['key'];
        //create signature 创建签名
        openssl_sign($this->salt, $signature, $private_key_pem, OPENSSL_ALGO_SHA256);
        //创建文件目录
        $privateKeyFile = $this->privateKeyFile;
        $publicKeyFile = $this->publicKeyFile;
        $signatureFile = $this->signatureFile;
        //检测目录是否存在，不存在则自动创建
        $position = strrpos($privateKeyFile, '/');
        $path = substr($privateKeyFile, 0, $position);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);//创建目录
        }
        //save for later 保存供以后使用
        file_put_contents($privateKeyFile, $private_key_pem);//私钥
        file_put_contents($publicKeyFile, $public_key_pem);//公钥
        file_put_contents($signatureFile, $signature);//签名
        //verify signature 验证签名
        $res = openssl_verify($this->salt, $signature, $public_key_pem, "sha256WithRSAEncryption");
        if ($res == 1) {
            $result = ['code' => 0, 'msg' => '生成成功', 'data' => ''];
        } elseif ($res == 0) {
            $result = ['code' => 1, 'msg' => '生成失败', 'data' => ''];
        } else {
            $errorInfo = 'error:' . openssl_error_string();
            $result = ['code' => 1, 'msg' => '生成失败', 'data' => "$errorInfo"];
        }
        return $result;
    }

    //--------------------------私钥加密,用公钥解密-------------------------
    /**
     * 私钥加密
     * @param $str string 需要加密的字符串
     * @return array 返回通过私钥加密的字符串
     */
    public function encPrivate($str)
    {
        $encrypted = "";
        //判断私钥是否可用
        $pi_key = openssl_pkey_get_private($this->private_key);
        openssl_private_encrypt($str, $encrypted, $pi_key);//私钥加密
        $data['encrypted'] = base64_encode($encrypted);
        return ['code' => 0, 'msg' => '私钥加密成功', 'data' => $data];
    }

    /**
     * 公钥解密
     * @param $encrypted string 通过私钥加密的字符串
     * @return array 返回原始字符串
     */
    public function decPublic($encrypted)
    {
        $decrypted = "";
        //私钥加密的内容通过公钥可用解密出来
        openssl_public_decrypt(base64_decode($encrypted), $decrypted, $pu_key);
        $data['decrypted'] = $decrypted;
        return ['code' => 0, 'msg' => '公钥解密成功', 'data' => $data];
    }

    //--------------------------公钥加密,用私钥解密-------------------------
    /**
     * 公钥加密
     * @param $str string 需要加密的字符串
     * @return array 返回通过公钥加密的字符串
     */
    public function encPublic($str)
    {
        $encrypted = "";
        //判断公钥是否可用
        $pu_key = openssl_pkey_get_public($this->public_key);
        openssl_public_encrypt($str, $encrypted, $pu_key);//公钥加密
        $data['encrypted'] = base64_encode($encrypted);
        return ['code' => 0, 'msg' => '公钥加密成功', 'data' => $data];
    }

    /**
     * 私钥解密
     * @param $encrypted string 通过公钥加密的字符串
     * @return array 返回原始字符串
     */
    public function dncPrivate($encrypted)
    {
        $decrypted = "";
        openssl_private_decrypt(base64_decode($encrypted), $decrypted, $pi_key);//私钥解密
        $data['decrypted'] = $decrypted;
        return ['code' => 0, 'msg' => '私钥解密成功', 'data' => $data];
    }
}