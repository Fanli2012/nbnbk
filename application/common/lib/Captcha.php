<?php

namespace app\common\lib;

/**
 * 验证码类
 */
class Captcha
{
    private $_fontfile = '';//字体文件
    private $_size = 20;//字体大小
    private $_width = 120;//画布宽度
    private $_height = 40;//画布高度
    private $_lenght = 4;//验证码长度
    private $_image = null;//画布资源
    private $_snow = 0;//雪花的个数
    private $_pixel = 0;//像素个数
    private $_line = 0;//线段个数

    /**
     *初始化数据
     * @param array $config 基本配置数组
     */
    function __construct($config = [])
    {
        if (is_array($config) && count($config) > 0) {
            //保证字体文件存在,是文件,而且有权限可读
            if (isset($config['fontfile']) && is_file($config['fontfile']) && is_readable($config['fontfile'])) {
                $this->_fontfile = $config['fontfile'];
            } else {
                return false;
            }
            //检测是否设置字体大小
            if (isset($config['size']) && $config['size'] > 0) {
                $this->_size = (int)$config['size'];
            }
            //检测室和设置了画布宽高
            if (isset($config['width']) && $config['width'] > 0) {
                $this->_width = (int)$config['width'];
            }
            if (isset($config['height']) && $config['height'] > 0) {
                $this->_height = (int)$config['height'];
            }
            //检测是否设置了验证码长度
            if (isset($config['lenght']) && $config['lenght'] > 0) {
                $this->_lenght = (int)$config['lenght'];
            }
            //设置判断干扰元素
            if (isset($config['snow']) && $config['snow'] > 0) {
                $this->_snow = (int)$config['snow'];
            }
            if (isset($config['pixel']) && $config['pixel'] > 0) {
                $this->_pixel = (int)$config['pixel'];
            }
            if (isset($config['line']) && $config['line'] > 0) {
                $this->_line = (int)$config['line'];
            }

            $this->_image = imagecreatetruecolor($this->_width, $this->_height);
            return $this->_image;
        } else {
            return false;
        }
    }

    /**
     *创建验证码
     */
    public function getCaptcha()
    {
        $white = imagecolorallocate($this->_image, 255, 255, 255);
        //填充矩形
        imagefilledrectangle($this->_image, 0, 0, $this->_width, $this->_height, $white);
        //生成验证码
        $str = $this->_generateStr($this->_lenght);
        if (FALSE == $str) {
            return false;
        }
        //将验证码存储到session中
        $_SESSION['code']=$str;
        //绘制验证码
        for ($i = 0; $i < $this->_lenght; $i++) {
            $size = $this->_size;
            $angle = mt_rand(-30, 30);
            $x = ceil($this->_width / $this->_lenght) * $i * mt_rand(5, 10);
            $y = ceil($this->_height / 1.5);
            //定义字体颜色
            $color = $this->$this->_getRandColor();
            $fontfile = $this->_fontfile;
            $text = mb_substr($str, $i, 1, 'utf-8');
            //在范围内随机定义坐标
            //imagestring($this->_image, $size, $x, $y, $text, $color);
            //使用指定的字体文件绘制文字
            imagettftext($this->_image, $size, $angle, $x, $y, $color, $fontfile, $text);
            //判断是否调用了干扰元素
            if ($this->_snow) {
                $this->_getSnow();
            } elseif ($this->_pixel) {
                $this->_getPixel();
            } elseif ($this->_line) {
                $this->_getLine();
            }
        }
        //输出图像
        header('content-type:image/png');
        imagepng($this->_image);
       /*
        //保存图像
        $fileName = 'captcha/' . microtime() . '.png';
        //检测目录是否存在，不存在则自动创建
        $position = strrpos($fileName, '/');
        $path = substr($fileName, 0, $position);
        //创建目录
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        //保存图片
        $res = file_put_contents($fileName,$this->_image);
        if($res != FALSE){
            //todo 存储图片和验证码，可考虑复用
        }
       */
        //销毁图像
        imagedestroy($this->_image);
        return strtolower($str);
    }

    /**
     * 干扰元素,雪花
     */
    private function _getSnow()
    {
        for ($i = 1; $i <= $this->_snow; $i++) {
            imagestring($this->_image, mt_rand(1, 5), mt_rand(0, $this->_width), mt_rand(0, $this->_height), '*', $this->_getRandColor());
        }
    }

    /**
     * 干扰元素,像素
     */
    private function _getPixel()
    {
        for ($i = 1; $i <= $this->_pixel; $i++) {
            imagesetpixel($this->_image, mt_rand(0, $this->_width), mt_rand(0, $this->_height), $this->_getRandColor());
        }
    }

    /**
     * 干扰元素,横线
     */
    private function _getLine()
    {
        for ($i = 1; $i <= $this->_line; $i++) {
            imageline($this->_image, mt_rand(0, $this->_width), mt_rand(0, $this->_height), mt_rand(0, $this->_width), mt_rand(0, $this->_height), $this->_getRandColor());
        }
    }

    /**
     *生成验证码字符
     * @param int $lenght 验证按字符长度
     * @return bool|string
     */
    private function _generateStr($lenght = 4)
    {
        if ($lenght < 1 || $lenght > 30) {
            return false;
        }
        $chars = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'i', 's', 't', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'M', 'N', 'P', 'Q', 'I', 'S', 'T', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        );
        //array_flip() 函数用于反转/交换数组中的键名和对应关联的键值
        //array_rand() 函数返回一个包含随机键名的数组
        $str = join('', array_rand(array_flip($chars), $lenght));
        return $str;
    }

    /**
     *生成随机颜色
     */
    private function _getRandColor()
    {
        return imagecolordeallocate($this->_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }

}