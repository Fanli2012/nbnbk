<?php
// +----------------------------------------------------------------------
// | 阿里云OSS服务
// +----------------------------------------------------------------------
namespace app\common\service;
require_once __DIR__ . '/../../../extend/mpdf/mpdf.php';

class Pdf
{
    const OSS_ACCESS_ID = '';
    const OSS_ACCESS_KEY = '';
    const OSS_ENDPOINT = 'oss-cn-beijing.aliyuncs.com';
    const OSS_TEST_BUCKET = 'cheyoubao';
    
    /**
     * 浏览器中浏览PDF
     */
    public static function mpdf2()
    {
        //实例化mpdf
        //设置中文编码
        $mpdf = new \mPDF('zh-cn','A4', 0, '宋体', 0, 0);
        //html内容
        $html='<h1><a name="top"></a>一个PDF文件</h1>';
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        exit;
    }
    
    /**
     * 生成PDF
     */
    public static function mpdf()
    {
        //实例化mpdf
        $mpdf = new \mPDF('utf-8','A4','','宋体',0,0,20,10);
         
        //设置字体,解决中文乱码
        $mpdf->useAdobeCJK = true;
        //$mpdf->SetAutoFont(AUTOFONT_ALL);//使用6.0以上版本不需要
         
        //获取要生成的静态文件
        $html = file_get_contents('http://www.thinkphp.cn/extend/728.html');
         
        echo $html;exit;
         
        //设置PDF页眉内容
        $header='<table width="95%" style="margin:0 auto;border-bottom: 1px solid #4F81BD; vertical-align: middle; font-family:
        serif; font-size: 9pt; color: #000088;"><tr>
        <td width="10%"></td>
        <td width="80%" align="center" style="font-size:16px;color:#A0A0A0">页眉</td>
        <td width="10%" style="text-align: right;"></td>
        </tr></table>';
         
        //设置PDF页脚内容
        $footer='<table width="100%" style=" vertical-align: bottom; font-family:
        serif; font-size: 9pt; color: #000088;"><tr style="height:30px"></tr><tr>
        <td width="10%"></td>
        <td width="80%" align="center" style="font-size:14px;color:#A0A0A0">页脚</td>
        <td width="10%" style="text-align: left;">页码：{PAGENO}/{nb}</td>
        </tr></table>';
         
        //添加页眉和页脚到pdf中
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
         
        //设置pdf显示方式
        $mpdf->SetDisplayMode('fullpage');
         
        //设置pdf的尺寸为270mm*397mm
        //$mpdf->WriteHTML('<pagebreak sheet-size="270mm 397mm" />');
         
        //创建pdf文件
        $mpdf->WriteHTML($html);
         
        //删除pdf第一页(由于设置pdf尺寸导致多出了一页)
        //$mpdf->DeletePages(1,1);
         
        //输出pdf
        $mpdf->Output();//可以写成下载此pdf   $mpdf->Output('文件名','D');
         
        exit;
    }

    public static function getBucketName()
    {
        return self::OSS_TEST_BUCKET;
    }

    /**
     * 工具方法，创建一个存储空间，如果发生异常直接exit
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $res = $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {
            return ['code'=>0, 'msg'=>$e->getMessage(), 'data'=>''];
        }
        
        return ['code'=>1, 'msg'=>'操作成功', 'data'=>$res];
    }
    
    /**
     * 上传指定的本地文件内容
     *
     * @param OssClient $ossClient OssClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public static function uploadFile($object, $filePath)
    {
        //$object = "oss-php-sdk-test/upload-test-object-name.txt";
        //$filePath = __FILE__;
        $options = array();
        $ossClient = self::getOssClient();
        $bucket = self::getBucketName();
        
        try {//self::createBucket();
            $res = $ossClient->uploadFile($bucket, $object, $filePath, $options);
        } catch (OssException $e) {
            return ['code'=>0, 'msg'=>$e->getMessage(), 'data'=>''];
        }
        
        return ['code'=>1, 'msg'=>'操作成功', 'data'=>$res];
    }
    
    /**
     * 把本地变量的内容到文件
     *
     * 简单上传,上传指定变量的内存值作为object的内容
     *
     * @param OssClient $ossClient OssClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public static function putObject($object, $filePath)
    {
        //$object = "oss-php-sdk-test/upload-test-object-name.txt";
        $content = file_get_contents($filePath);
        $options = array();
        $ossClient = self::getOssClient();
        $bucket = self::getBucketName();
        
        try {
            $res = $ossClient->putObject($bucket, $object, $content, $options);
        } catch (OssException $e) {
            return ['code'=>0, 'msg'=>$e->getMessage(), 'data'=>''];
        }
        
        return ['code'=>1, 'msg'=>'操作成功', 'data'=>$res];
    }
}