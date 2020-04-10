<?php
require_once FNT_DIR_CORE_INCLUDES . '/abstract.php';
class Fnt_ProductImportExport extends Fnt_Core{

    protected $import;
    protected $export;
    protected $template;

    /**
     * @return Fnt_ExcelExport
     */
    public function get_export()
    {
        return $this->export;
    }

    /**
     * @param Fnt_ExcelExport $export
     */
    public function set_export($export)
    {
        $this->export = $export;
    }

    /**
     * @return Fnt_ExcelImport
     */
    public function get_import()
    {
        return $this->import;
    }

    /**
     * @param Fnt_ExcelImport $import
     */
    public function set_import($import)
    {
        $this->import = $import;
    }

    /**
     * @return Fnt_ExcelTemplate
     */
    public function get_template()
    {
        return $this->template;
    }

    /**
     * @param Fnt_ExcelTemplate $template
     */
    public function set_template($template)
    {
        $this->template = $template;
    }

    public function __construct(){
        require_once(FNT_DIR_EXCEL_INCLUDES . '/import.php');
        require_once(FNT_DIR_EXCEL_INCLUDES . '/export.php');
        require_once(FNT_DIR_EXCEL_INCLUDES . '/template.php');
        ini_set("memory_limit", "512M");
        ini_set("upload_max_filesize", "512M");
        ini_set("post_max_size", "512M");
        ini_set('max_execution_time', 3600);
        $this->import = new Fnt_ExcelImport();
        $this->export = new Fnt_ExcelExport();
        $this->template = new Fnt_ExcelTemplate();
    }

    public static function get_export_columns_setting(){
        return get_option('fnt-export-columns-setting',array());
    }

    public static function get_template_columns_setting(){
        return get_option('fnt-template-columns-setting',array());
    }

    public static function set_excel_import_error($value) {
        $value = serialize($value);
        return update_option('fnt-import-error-list', $value);
    }

    public static function get_excel_import_error() {
        $value = get_option('fnt-import-error-list', array());
        return unserialize($value);
    }
    public static function set_excel_import_file_name($value) {
        $value = serialize($value);
        return update_option('fnt-import-file-name', $value);
    }

    public static function get_excel_import_file_name() {
        $value = get_option('fnt-import-file-name', array());
        return unserialize($value);
    }
}