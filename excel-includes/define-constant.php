<?php
class Fnt_ExcelDefineConstant {
    public static $excel_config_sheet_name = 'Product_Config';
    public static $excel_data_sheet_name = 'Products_Data';
    public static $excel_dropdownlist_sheet_name = 'Products_Dropdownlist';
    public static $excel_password = 'fnt';
    public static $product_type_cell = 'C1';
    public static $product_count_cell = 'C2';
    public static $attribute_count_cell = 'C3';
    public static $product_attribute_cell = 'D';
    public static $product_column_name_cell = 'A';
    public static $product_column_excel_cell = 'B';
    public static $product_dropdownlist_cell = 'A';
    public static $num_row_populate_template = 100;
    public static $freeze_pane = 'A2';
    public static $excel_header_row_style = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => 'F1F1F1'),
            'size'  => 12,
            'name'  => 'Verdana'
        ),
        'fill' => array(
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => '4a32d3')
        )
    );
    public static $excel_content_row_style = array(
        'font'  => array(
            'bold'  => false,
            'color' => array('rgb' => '000'),
            'size'  => 12,
            'name'  => 'Verdana'
        ),
        'fill' => array(
            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'F1F1F1')
        )
    );

    public static $columns_for_excel = array(
        Fnt_ProductListCons::COLUMN_TAG               => Fnt_ProductListCons::COLUMN_TAG,
        Fnt_ProductListCons::COLUMN_SKU               => Fnt_ProductListCons::COLUMN_SKU,
        Fnt_ProductListCons::COLUMN_CONTENT           => Fnt_ProductListCons::COLUMN_CONTENT,
        Fnt_ProductListCons::COLUMN_EXCERPT           => Fnt_ProductListCons::COLUMN_EXCERPT,
        Fnt_ProductListCons::COLUMN_STOCK             => Fnt_ProductListCons::COLUMN_STOCK,
        Fnt_ProductListCons::COLUMN_MANAGE_STOCK      => Fnt_ProductListCons::COLUMN_MANAGE_STOCK,
        Fnt_ProductListCons::COLUMN_STOCK_QUANTITY    => Fnt_ProductListCons::COLUMN_STOCK_QUANTITY,
        Fnt_ProductListCons::COLUMN_BACK_ORDERS       => Fnt_ProductListCons::COLUMN_BACK_ORDERS,
        Fnt_ProductListCons::COLUMN_SOLD_INDIVIDUALLY => Fnt_ProductListCons::COLUMN_SOLD_INDIVIDUALLY,
        Fnt_ProductListCons::COLUMN_REGULAR_PRICE     => Fnt_ProductListCons::COLUMN_REGULAR_PRICE,
        Fnt_ProductListCons::COLUMN_SALE_PRICE        => Fnt_ProductListCons::COLUMN_SALE_PRICE,
        Fnt_ProductListCons::COLUMN_FEATURE           => Fnt_ProductListCons::COLUMN_FEATURE,
        Fnt_ProductListCons::COLUMN_DATE              => Fnt_ProductListCons::COLUMN_DATE,
        Fnt_ProductListCons::COLUMN_WEIGHT            => Fnt_ProductListCons::COLUMN_WEIGHT,
        Fnt_ProductListCons::COLUMN_LENGTH            => Fnt_ProductListCons::COLUMN_LENGTH,
        Fnt_ProductListCons::COLUMN_WIDTH             => Fnt_ProductListCons::COLUMN_WIDTH,
        Fnt_ProductListCons::COLUMN_HEIGHT            => Fnt_ProductListCons::COLUMN_HEIGHT,
        Fnt_ProductListCons::COLUMN_ALLOW_COMMENTS    => Fnt_ProductListCons::COLUMN_ALLOW_COMMENTS
    );
    public static $columns_default_for_excel = array(
        Fnt_ProductListCons::COLUMN_ID   => Fnt_ProductListCons::COLUMN_ID,
        Fnt_ProductListCons::COLUMN_TYPE => Fnt_ProductListCons::COLUMN_TYPE,
        Fnt_ProductListCons::COLUMN_NAME => Fnt_ProductListCons::COLUMN_NAME
    );
    public static $columns_for_variation = array(
        Fnt_ProductListCons::COLUMN_SKU            => Fnt_ProductListCons::COLUMN_SKU,
        Fnt_ProductListCons::COLUMN_REGULAR_PRICE  => Fnt_ProductListCons::COLUMN_REGULAR_PRICE,
        Fnt_ProductListCons::COLUMN_SALE_PRICE     => Fnt_ProductListCons::COLUMN_SALE_PRICE,
        Fnt_ProductListCons::COLUMN_STOCK          => Fnt_ProductListCons::COLUMN_STOCK
    );
    public static $value_default_for_string = '';
    public static $value_default_for_number = 0;
    public static $value_special = array(
        '#NUM!',
        '#VALUE!',
        '#REF!'
    );
}