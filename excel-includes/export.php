<?php
require_once(FNT_DIR_EXCEL_INCLUDES . '/utils.php');
class Fnt_ExcelExport extends Fnt_ExcelUtils{
    private $exporting_products_data = null;
    private $selected_column_to_export;
    private $column_name_in_excel = array();

    public function __construct() {
        parent::__construct();
    }

    // get columns, which will be export to localize script to javascript
    public static function get_array_column_for_js() {
        $result = array();
        foreach(Fnt_ExcelDefineConstant::$columns_for_excel as $column) {
            $temp_array = array();
            $temp_array['label'] = Fnt_ProductListCons::$column_table_display[$column];
            $temp_array['value'] = $column;
            $result[] = $temp_array;
        }
        return $result;
    }
    // get products to export by the query in current table
    public function get_products_for_export($query_products_url) {
        global $wpdb;
        $sql = Fnt_Url_Handler::prepare_query('get_all_data', $query_products_url);
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        $products = array();
        $_pf = new WC_Product_Factory_Custom();
        foreach($result as $product) {
            $products[] = $_pf->get_product($product['ID']);
        }
        return $products;
    }
    public function proceed_exporting_products($selected_export_columns_setting, $query_products_url){
        $this->exporting_products_data = $this->get_products_for_export($query_products_url);
        $default_columns = Fnt_ExcelDefineConstant::$columns_default_for_excel;
        foreach($selected_export_columns_setting as $selected_export_column_setting) {
            $default_columns[$selected_export_column_setting] = $selected_export_column_setting;
        }
        $this->selected_column_to_export = $default_columns;
        $this->column_name_in_excel = self::prepare_column_name_excel($this->selected_column_to_export);

        $this->populate_product_data_to_worksheet();
    }
    /**
     * get array content column excel
     * @param $columns
     * @return array
     */
    public static function prepare_column_name_excel( $columns ) {
        $result = array();
        $column_index = 0;
        foreach( $columns as $column ) {
            $result[ $column ] = PHPExcel_Cell::stringFromColumnIndex( $column_index );
            $column_index ++;
        }
        return $result;
    }
    /**
     * @param $objWorkSheet
     * @param $column_excel
     * @param $i
     * @return mixed
     */
    public static function validation_cell_number($objWorkSheet, $column_excel, $i) {
        $objValidation = $objWorkSheet->getCell($column_excel . $i)->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_DECIMAL);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setErrorTitle(__('Input error', 'fnt'));
        $objValidation->setError(__('Please input number.', 'fnt'));
        $objValidation->setPromptTitle(__('Enter number', 'fnt'));
        $objValidation->setPrompt(__('Please pick a value as number.', 'fnt'));
    }
    /**
     * @param $objWorkSheet
     * @param $column_excel
     * @param $i
     * @return mixed
     */
    public static function validation_cell_text($objWorkSheet, $column_excel, $i) {
        $objValidation = $objWorkSheet->getCell($column_excel . $i)->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_DECIMAL);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setErrorTitle(__('Input error', 'fnt'));
        $objValidation->setError(__('Please input text.', 'fnt'));
        $objValidation->setPromptTitle(__('Enter text', 'fnt'));
        $objValidation->setPrompt(__('Please pick a value as text.', 'fnt'));
    }
    /**
     * @param $objWorkSheet
     * @param $column_excel
     * @param $i
     * @return mixed
     */
    public static function validation_cell_text_attribute($objWorkSheet, $column_excel, $i) {
        $objValidation = $objWorkSheet->getCell($column_excel . $i)->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_DECIMAL);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setErrorTitle('Input error');
        $objValidation->setError('Please input correct format.');
        $objValidation->setPromptTitle('Enter attribute type Text');
        $objValidation->setPrompt('Please pick a value with "|" separate terms.');
    }
    /**
     * @param $objWorkSheet
     * @param $column_excel
     * @param $i
     * @return mixed
     */
    public static function validation_cell_only_view($objWorkSheet, $column_excel, $i) {
        $objValidation = $objWorkSheet->getCell($column_excel . $i)->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_DECIMAL);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setErrorTitle(__('Protected column.', 'fnt'));
        $objValidation->setError(__("Please don't change this.", 'fnt'));
        $objValidation->setPromptTitle(__("Protected column.", 'fnt'));
        $objValidation->setPrompt(__("Don't change this, this field is only view.", 'fnt'));
    }
    /**
     * Fill data sheet
     * @param PHPExcel $objPHPExcel
     * @param $product_data
     */
    private function proceed_populate_data_to_worksheet(&$objPHPExcel, $product_data) {
        try {
            $objPHPExcel->setActiveSheetIndexByName(Fnt_ExcelDefineConstant::$excel_data_sheet_name);
            $objWorkSheet = $objPHPExcel->getActiveSheet();

            $columns = $this->selected_column_to_export;

            $i = 1;
            foreach($columns as $column) {
                $column_excel = $this->column_name_in_excel[$column];
                $objWorkSheet->setCellValue($column_excel.$i, Fnt_ProductListCons::$column_table_display[$column]);
                $styleArray = Fnt_ExcelDefineConstant::$excel_header_row_style;
                $objWorkSheet->getStyle($column_excel.$i)->applyFromArray($styleArray);
                $objWorkSheet->getColumnDimension($column_excel)->setWidth(max(strlen(Fnt_ProductListCons::$column_table_display[$column]), 25));
            }
            $i = 2;
            $begin_index = $i;
            $range = null;
            $array_dropdown = array();

            foreach ($product_data as $item) {
                foreach($columns as $column) {
                    $value = '';
                    $column_name_in_db = trim(Fnt_ProductListCons::$column_name_in_db[$column], '_');
                    if(Fnt_ProductListCons::$column_table_in_db[$column] == 'posts') {
                        $value = $item->get_post_data()->$column_name_in_db;
                    }
                    else {
                        $value = $item->$column_name_in_db;
                    }
                    $column_excel = $this->column_name_in_excel[$column];
                    switch($column) {
                        case Fnt_ProductListCons::COLUMN_ID:
                            $this->validation_cell_only_view($objWorkSheet, $column_excel, $i);
                            break;
                        case Fnt_ProductListCons::COLUMN_TYPE:
                            $this->validation_cell_only_view($objWorkSheet, $column_excel, $i);
                            $value = $item->get_type();
                            break;
                        case Fnt_ProductListCons::COLUMN_DATE:
                            $this->validation_cell_only_view($objWorkSheet, $column_excel, $i);
                            break;
                        case Fnt_ProductListCons::COLUMN_TAG:
                            $value = $item->get_product_list_term_meta( $column, 'name' );
                            break;
                        case Fnt_ProductListCons::COLUMN_CATEGORIES:
                        case Fnt_ProductListCons::COLUMN_SHIPPING_CLASS:
                            // populate for dropdown
                            $this->validation_cell_dropdwonlist($objPHPExcel, $column_excel, $i, $column, $array_dropdown);
                            // fill value for cell
                            // todo: make correct value, add grade for value
                            if(is_array($value) && sizeof($value)>0 && !empty($value[0])) {
                                if($value[0]->parent == 0) {
                                    $value_cell = $value[0]['term_id'].': '.$value[0]['name'];
                                } else {
                                    $value_cell = '  '.$value[0]['term_id'].': '.$value[0]['name'];
                                }
                            } else {
                                $value_cell = '';
                            }
                            $value = $value_cell;
                            break;
                        default:
                            if(Fnt_ProductListCons::$column_format[$column] == 'int' || Fnt_ProductListCons::$column_format[$column] == 'float') {
                                $this->validation_cell_number($objWorkSheet, $column_excel, $i);
                            }
                            if(array_key_exists($column, Fnt_ProductListCons::$column_mapping)) {
                                $this->validation_cell_dropdwonlist($objPHPExcel, $column_excel, $i, $column, $array_dropdown);
                                $value = Fnt_ProductListCons::$column_mapping[$column][$value];
                            }
                            break;
                    }
                    if(is_array($value)) {
                        $value = '';
                    }
                    $objWorkSheet->setCellValue($column_excel . $i, ''. $value);
                    $objWorkSheet->getStyle($column_excel . $i)->getFont()->setSize(12);
                }
                $i++;
            }
            $last_cell = ''.$this->column_name_in_excel[end($columns)].($i-1);
            $objWorkSheet->getProtection()->setPassword(Fnt_ExcelDefineConstant::$excel_password);
            $objWorkSheet->getProtection()->setSheet(true);
            $objWorkSheet->getStyle('C2:'.$last_cell)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

            // array of columns want to lock
            $array_lock_columns = array(
                Fnt_ProductListCons::COLUMN_DATE
            );
            // begin check and lock that columns
            if ( ! empty( $array_lock_columns ) ) {
                foreach ( $array_lock_columns as $column ) {
                    if ( isset(  $this->column_name_in_excel[$column] ) ) {
                        // Lock cell to can't editable
                        $column_excel = $this->column_name_in_excel[$column];
                        // example: L2:L5
                        $objWorkSheet->getStyle( $column_excel . $begin_index . ':' . $column_excel . ( $i - 1 ) )->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
                    }
                }
            }

            $objWorkSheet->freezePane(Fnt_ExcelDefineConstant::$freeze_pane);

            $this->add_config_sheet($objPHPExcel, $this->selected_column_to_export, $this->column_name_in_excel);
        }catch(Exception $ex){

        }
    }
    public static function validation_cell_dropdwonlist(&$objPHPExcel, $column_excel, $i, $column, &$array_dropdown) {
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        $objValidation = $objWorkSheet->getCell($column_excel . $i)->getDataValidation();
        $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
        $objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle(__('Input error', 'fnt'));
        $objValidation->setError(__('Value is not in list.', 'fnt'));
        $objValidation->setPromptTitle(__('Pick from list', 'fnt'));
        $objValidation->setPrompt(__('Please pick a value from the drop-down list.', 'fnt'));

        $range = '';
        if(!isset($array_dropdown[$column]) && empty($array_dropdown[$column])) {
            $args = Fnt_Core::get_dropdownlist($column);
            if(!empty($args)) {
                $range = self::add_dropdownlist_sheet($objPHPExcel, $args);
                $array_dropdown[$column] = $range;
            }
        }
        if(isset($array_dropdown[$column]) && !empty($array_dropdown[$column])) {
            $range = $array_dropdown[$column];
        }
        if(!empty($range)) {
            $objValidation->setFormula1($range);
        }
    }
    public static function add_dropdownlist_sheet(&$objPHPExcel, $args = array()) {
        $range = '';
        $column_excel = Fnt_ExcelDefineConstant::$product_dropdownlist_cell;
        $is_first_row = false;

        if(!($objPHPExcel->sheetNameExists(Fnt_ExcelDefineConstant::$excel_dropdownlist_sheet_name))) {
            $ProductDataWorksheet = new PHPExcel_Worksheet($objPHPExcel);
            $ProductDataWorksheet->setTitle(Fnt_ExcelDefineConstant::$excel_dropdownlist_sheet_name);
            $objPHPExcel->addSheet($ProductDataWorksheet);
        }
        $objPHPExcel->setActiveSheetIndexByName(Fnt_ExcelDefineConstant::$excel_dropdownlist_sheet_name);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        $objWorkSheet->getProtection()->setPassword(Fnt_ExcelDefineConstant::$excel_password);
        $objWorkSheet->getProtection()->setSheet(true);
        $templateHighestRow = $objWorkSheet->getHighestRow();
        $i = $templateHighestRow;
        $cell_value = $objWorkSheet->getCell(Fnt_ExcelDefineConstant::$product_dropdownlist_cell.$i)->getCalculatedValue();
        if($i == 1 && $cell_value==null) {
            $is_first_row = true;
        }
        if(!$is_first_row) {
            $i++;
        }
        $range .= '$'.$column_excel.'$'.$i;
        foreach ($args as $arg) {
            $objWorkSheet->setCellValue($column_excel . $i, $arg);
            $objWorkSheet->getColumnDimension($column_excel)->setWidth(max(strlen($arg), 32));
            $i++;
        }
        $i--;
        $range .= ':';
        $range .= '$'.$column_excel.'$'.$i;
        $objWorkSheet->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);

        $result = Fnt_ExcelDefineConstant::$excel_dropdownlist_sheet_name.'!'.$range;
        return $result;
    }

    /**
     * @param $objPHPExcel
     * @param $selected_column_to_export
     * @param $column_name_in_excel
     * @param string $product_type
     * @param array $product_attributes
     */
    public static function add_config_sheet(&$objPHPExcel, $selected_column_to_export, $column_name_in_excel, $product_type='', $product_attributes = array()) {
        $ProductDataWorksheet = new PHPExcel_Worksheet($objPHPExcel);
        $ProductDataWorksheet->setTitle(Fnt_ExcelDefineConstant::$excel_config_sheet_name);
        $objPHPExcel->addSheet($ProductDataWorksheet);
        $objPHPExcel->setActiveSheetIndexByName(Fnt_ExcelDefineConstant::$excel_config_sheet_name);
        $objWorkSheet = $objPHPExcel->getActiveSheet();

        $objWorkSheet->getProtection()->setPassword(Fnt_ExcelDefineConstant::$excel_password);
        $objWorkSheet->getProtection()->setSheet(true);

        $columns = $selected_column_to_export;
        $i = 1;
        foreach ($columns as $column) {
            $column_excel = $column_name_in_excel[$column];

            $objWorkSheet->setCellValue(Fnt_ExcelDefineConstant::$product_column_name_cell . $i, $column);
            $objWorkSheet->setCellValue(Fnt_ExcelDefineConstant::$product_column_excel_cell . $i, $column_excel);

            $objWorkSheet->getColumnDimension($column_excel)->setWidth(max(strlen($column), 32));
            $i++;
        }
        $i = 1;
        foreach ($product_attributes as $attribute) {
            $objWorkSheet->setCellValue(Fnt_ExcelDefineConstant::$product_attribute_cell . $i, $attribute);
            $objWorkSheet->getColumnDimension(Fnt_ExcelDefineConstant::$product_attribute_cell)->setWidth(max(strlen($attribute), 32));
            $i++;
        }

        $objWorkSheet->setCellValue(Fnt_ExcelDefineConstant::$attribute_count_cell, sizeof($product_attributes));
        $objWorkSheet->setCellValue(Fnt_ExcelDefineConstant::$product_type_cell, $product_type);

        $objWorkSheet->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
    }
    private function populate_product_data_to_worksheet(){
        if(!empty($this->exporting_products_data)){
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator('VinaEcom')
                ->setLastModifiedBy('VinaEcom')
                ->setTitle(__('Exporting Products', 'fnt'))
                ->setSubject('configuration')
                ->setDescription('configuration')
                ->setKeywords('configuration')
                ->setCategory('configuration');
            $ProductDataWorksheet = new PHPExcel_Worksheet($objPHPExcel);
            $ProductDataWorksheet->setTitle(Fnt_ExcelDefineConstant::$excel_data_sheet_name);
            $objPHPExcel->addSheet($ProductDataWorksheet);
            $this->proceed_populate_data_to_worksheet($objPHPExcel, $this->exporting_products_data);

            if($objPHPExcel->sheetNameExists('Worksheet')) {
                $objPHPExcel->setActiveSheetIndexByName('Worksheet');
                $objPHPExcel->removeSheetByIndex($objPHPExcel->getActiveSheetIndex());
            }
            $objPHPExcel->setActiveSheetIndexByName(Fnt_ExcelDefineConstant::$excel_data_sheet_name);
            $file_name_export = 'export-edit-'.current_time( 'Y-m-d-H-m-s');
            self::output_to_excel_file($objPHPExcel, $file_name_export, '.xlsx', 'save_file');
        }
    }
    public static function output_to_excel_file($objPHPExcel,$export_file_name, $ext='.xls', $outputType='php'){
        if($ext==".xls") {
            $writerType='Excel5';
            header('Content-Type: application/vnd.ms-excel');
        } else {
            $writerType='Excel2007';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
        $filename_to_export = $export_file_name.$ext;
        header('Content-Disposition: attachment;filename="'.$filename_to_export.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $writerType);
        if($outputType == 'php'){
            $objWriter->save('php://output');
            exit();
        } else {
            $path_to_save_folder = FNT_DIR_EXPORT;
            $url_to_save_folder = FNT_URL_EXPORT;
            if(!file_exists($path_to_save_folder)){
                mkdir($path_to_save_folder);
            }
            if(!file_exists($path_to_save_folder . '/index.html')){
                $fp = fopen($path_to_save_folder . '/index.html','a');
                fwrite($fp, sprintf("<h1>%s</h1>", __('Page Not Found', 'fnt')));
                fclose($fp);
            }
            $objWriter->save($path_to_save_folder . '/' . $filename_to_export);
            header('Location: ' . $url_to_save_folder . '/' . $filename_to_export);
            exit();
        }
    }
}