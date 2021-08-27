<?php

namespace app\common\logic;

use app\common\lib\ReturnData;

class ExcelLogic extends BaseLogic
{
    /**
     * Excel数据导出
     * @param array $title 标题行名称
     * @param array $data 导出数据
     * @param string $fileName 文件名
     * @param string $savePath 保存路径
     * @param $type   是否下载  false保存,true下载
     * @return string 返回文件全路径
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function export_excel($title = array(), $data = array(), $fileName = '', $savePath = './', $isDown = false)
    {
        include(EXTEND_PATH . 'PHPExcel/PHPExcel.php');
        $obj = new \PHPExcel();

        //横向单元格标识
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $obj->getActiveSheet(0)->setTitle('sheet名称'); //设置sheet名称
        $_row = 1; //设置纵向单元格标识
        //填写数据  
        if ($data) {
            $i = 0;
            foreach ($data AS $_v) {
                $j = 0;
                foreach ($_v AS $_cell) {
                    $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + $_row), $_cell);
                    $j++;
                }

                $i++;
            }
        }

        //文件名处理
        if (!$fileName) {
            $fileName = uniqid(time(), true);
        }

        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');

        //网页下载
        if ($isDown) {
            header('pragma:public');
            header("Content-Disposition:attachment;filename=$fileName.xlsx");
            $objWrite->save('php://output');
            exit;
        }

        $_fileName = iconv('utf-8', 'gb2312', $fileName); //转码
        $_savePath = $savePath . $_fileName . '.xlsx';
        $objWrite->save($_savePath);

        return $savePath . $fileName . '.xlsx';
    }

}