<?php
class ControllerCommonBase extends Controller {

    public function redirect($url) {
        $data = array(
            'url' => $url
        );
        echo $this->load->view('common/redirect', $data);
        exit();
    }

    // 添加管理员操作日志
    public function adminLog($description) {
        //加载管理员操作日志 model
        $this->load->library('sys_model/admin_log', true);

        $admin_id = $this->logic_admin->getId();
        $admin_name = $this->logic_admin->getadmin_name();
        $ip = $this->request->ip_address();
        $curDate = date('Y-m-d H:i:s');

        $data = array(
            'admin_id' => $admin_id,
            'admin_name' => $admin_name,
            'log_description' => $description,
            'log_ip' => $ip,
            'log_time' => $curDate
        );
        $this->sys_model_admin_log->addAdminLog($data);
    }

    // 获取信息中心数据
    public function statisticsMessages() {

        $this->load->library('sys_model/fault', true);
        $this->load->library('sys_model/feedback', true);

        $condition = array(
            'processed' => 0
        );

        $violations = $this->sys_model_fault->getTotalIllegalParking($condition);
        $faults = $this->sys_model_fault->getTotalFaults($condition);
        $feedbacks = $this->sys_model_feedback->getTotalFeedbacks($condition);
        $amount = $violations + $faults + $feedbacks;

        return array(
            'amount' => $amount,
            'violations' => $violations,
            'faults' => $faults,
            'feedbacks' => $feedbacks,
        );
    }

    /**
     * 导入EXCEL
     * @return array|bool
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function importExcel() {
        $pathFormat =  'excel/{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:3}';
        $maxSize = 2 * 1024 * 1024;         // 2M
        $allowFiles = array('.xls');

        $config = array(
            'pathFormat' => $pathFormat,
            "maxSize" => $maxSize,
            "allowFiles" => $allowFiles
        );
        // 文件变量名称
        $fileField = 'upfile';
        // 文件类型
        $type = 'upload';
        $up =  new Uploader($fileField, $config, $type, $this->request->files);
        $info = $up->getFileInfo();

        if ($info['state'] != 'SUCCESS') { // 上传失败
            $this->response->showErrorResult($info['state']);
            return false;
        }

        $filePath = DIR_STATIC . $info['filePath'];

        // 加载PHPExcel 工厂类
//        library('PHPExcel/IOFactory');
        require_once DIR_SYSTEM . "library/PHPExcel/IOFactory.php";
        $objReader = IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filePath);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        // 删除源文件
        if (is_file($filePath) && file_exists($filePath)) {
            @unlink($filePath);
        }
        return $excelData;
    }

    /**
     * 导出EXCEL
     */
    public function exportExcel($data) {
        // 数据大导出时间较长，暂时用延长响应超时时间
        set_time_limit(0);
        // 加载PHPExcel 工厂类
//        library('PHPExcel/IOFactory');
        require_once DIR_SYSTEM . "library/PHPExcel/IOFactory.php";
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");


        $keys = array_keys($data['header']);
        $count = count($keys);
        // 合并标题单元格
        $objPHPExcel->getActiveSheet()->mergeCells('A1:'.chr(65 + $count - 1).'1');

        // 字体和样式
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
        // 标题字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        // 标题水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // 标题行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        // 表头字体加粗
        $objPHPExcel->getActiveSheet()->getStyle('A2:'.chr(65 + $count - 1).'2')->getFont()->setBold(true);
        // 表头水平居中
        $objPHPExcel->getActiveSheet()->getStyle('A2:'.chr(65 + $count - 1).'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        // 表头行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);

        // 标题内容
        $sheet  = $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $data['title']);
        // 表头内容
        for ($i = 0; $i < $count; $i++) {
            // 设置列宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension(chr(65 + $i))->setWidth(20);
            $sheet->setCellValue(chr(65 + $i) . '2', $data['header'][$keys[$i]]);
        }

        // 内容
        $i = 0;
        foreach ($data['list'] as $value) {
            // 设置行高
            $objPHPExcel->getActiveSheet()->getRowDimension($i + 3)->setRowHeight(25);
            // 水平居中
            $objPHPExcel->getActiveSheet()->getStyle('A'.($i + 3).':'.chr(65 + $count -1).($i + 3))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            for ($j = 0; $j < $count; $j++) {
                // 单元格格式为文本
                $objPHPExcel->getActiveSheet()->setCellValueExplicit(chr(65 + $j) . ($i + 3), $value[$keys[$j]],PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $i++;
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle($data['title']);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // 清除缓冲区
        ob_end_clean();
        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . ((isset($data['filename']) && !empty($data['filename'])) ? $data['filename'] : $data['title']) . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}
