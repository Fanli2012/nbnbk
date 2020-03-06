<?php

namespace app\index\controller;

use think\Db;
use think\Log;
use think\Request;
use think\Session;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ShopLogic;

class Test extends Base
{
    //图片上传
    public function formUploadimg()
    {
        return $this->fetch();
    }

    //图片上传
    public function zip($files, $create_zip_absolute_path)
    {
        /* $files = array(
            array('file_path' => $_SERVER['DOCUMENT_ROOT'] . "/uploads/wxacode/85a9166c51ca4ae6258f4609d1f66d21.jpg", 'file_name' => '是VS.jpg'),
            array('file_path' => $_SERVER['DOCUMENT_ROOT'] . "/uploads/wxacode/123.jpg", 'file_name' => '测试2/测试2.jpg')
        );
        $time = time();
		$create_zip_absolute_path = $_SERVER['DOCUMENT_ROOT'] . "/uploads/" . $time . ".zip"; //下载后的文件名 */

        $zip = new \ZipArchive(); //新建一个ZipArchive的对象

        //开始操作压缩包
        if ($zip->open($create_zip_absolute_path, \ZipArchive::CREATE) === TRUE) {
            foreach ($files as $k => $v) {
                $zip->addFromString(iconv('utf-8', 'gbk//ignore', $v['file_name']), file_get_contents($v['file_path'])); //向压缩包中添加文件
            }

            $zip->close(); //关闭压缩包
        }

        exit;


        $path = $_SERVER['DOCUMENT_ROOT'] . "/uploads/wxacode"; //要压缩的文件的绝对路径，最后不要加/
        $filename = 'niao'; //生成压缩文件名
        $path = iconv("UTF-8", "GBK", $path); //加这行中文文件夹也ok了

        $this->zipDir($path, $path . '/../out.zip');
        exit;

        $this->create_zip($path, $filename);
        if (!file_exists('./' . $filename . '.zip')) {
            echo 1;
            die;
        }
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($filename . '.zip')); //文件名
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
        header('Content-Length: ' . filesize('./' . $filename . '.zip')); //告诉浏览器，文件大小
        @readfile('./' . $filename . '.zip'); //下载到本地
        @unlink('./' . $filename . '.zip'); //删除服务器上生成的这个压缩文件
    }

    public function create_zip($path, $filename)
    {
        $zip = new \ZipArchive();
        try {
            if ($zip->open($filename . '.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
                $this->addFileToZip($path, $zip, $path);//调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
                $zip->close(); //关闭处理的zip文件
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * 压缩一个目录
     * @param $path 文件夹路径
     * @param $zip zip 对象
     */
    public function addFileToZip($path, $zip)
    {
        $handler = opendir($path); //打开当前文件夹由$path指定。

        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path . "/" . $filename, $zip);
                } else { //将文件加入zip对象
                    $zip->addFile($path . "/" . $filename);
                }
            }
        }
        @closedir($path);
    }

    /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     */
    function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    $this->folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        @closedir($handle);
    }

    /**
     * Zip a folder (include itself).
     * Usage:
     *   zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    function zipDir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $z = new \ZipArchive();
        $z->open($outZipPath, \ZipArchive::CREATE);
        $z->addEmptyDir($dirName);
        $this->folderToZip($sourcePath, $z, strlen("$parentPath/"));
        $z->close();
    }

}