<?php
require_once(FNT_DIR_LIBRARY . '/PHPExcel/Classes/PHPExcel.php');
require_once(FNT_DIR_EXCEL_INCLUDES . '/define-constant.php');
abstract class Fnt_ExcelUtils {

    public function __construct() {

    }

    public function check_excel_file_is_2003_or_2007($filename){
        $basename = basename($filename);
        $name = explode(".", $basename);
        $ext = sizeof($name) > 1 ? $name[sizeof($name) - 1] : '';
        return $ext == 'xls' || $ext == 'xlsx' ?  true : false;
    }

} 