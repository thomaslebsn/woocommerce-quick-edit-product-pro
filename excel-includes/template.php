<?php
require_once( FNT_DIR_EXCEL_INCLUDES . '/utils.php' );
require_once( FNT_DIR_EXCEL_INCLUDES . '/export.php' );
class Fnt_ExcelTemplate extends Fnt_ExcelUtils {

    private $selected_column_to_export;
    private $selected_column_value_to_export;
    private $column_name_in_excel = array();
    private $product_type;
    private $attribute_columns = array();
    private $attributes;
    public function __construct() {
        parent::__construct();
    }

    public function proceed_exporting_template_products_by_cols_setting( $selected_export_columns_setting ) {
        try {
            $this->product_type = $selected_export_columns_setting['selected_product_type'];
        } catch ( Exception $ex ) {
            $this->product_type = '';
        }
        if ( $this->product_type != '' ) {
            $have_columns_product_type = isset( Fnt_ProductListCons::$column_product_type[ $this->product_type ] ) && ! empty( Fnt_ProductListCons::$column_product_type[ $this->product_type ] );
            $columns_product_type = $have_columns_product_type ? Fnt_ProductListCons::$column_product_type[ $this->product_type ] : array();
            $have_columns_data_product_type  = isset( Fnt_ProductListCons::$column_product_type[ $this->product_type ] ) && ! empty( Fnt_ProductListCons::$column_product_type[ $this->product_type ] );
            $columns_data_product_type = $have_columns_data_product_type ? Fnt_ProductListCons::$column_product_type[ $this->product_type ] : array();
            if ( $this->product_type == 'variable' ) { // add more columns
                $have_selected_attribute = isset( $selected_export_columns_setting['selected_attribute'] ) && ! empty( $selected_export_columns_setting['selected_attribute'] );
                $this->attribute_columns = $have_selected_attribute ? $selected_export_columns_setting['selected_attribute'] : array();
                $this->attributes = Fnt_Core::get_product_attributes_by_list_name( Fnt_Core::format_array_to_list_string( $this->attribute_columns ) );
                $columns_product_type = array_merge( array( Fnt_ProductListCons::COLUMN_RELATIONSHIP => Fnt_ProductListCons::COLUMN_RELATIONSHIP ), $this->attribute_columns, Fnt_ExcelDefineConstant::$columns_for_variation, $columns_product_type );
                $columns_data_product_type = array_merge( array( Fnt_ProductListCons::COLUMN_RELATIONSHIP => Fnt_ProductListCons::COLUMN_RELATIONSHIP ), $this->attributes, Fnt_ExcelDefineConstant::$columns_for_variation, $columns_data_product_type );
            }
        } else {
            $columns_product_type = array();
            $columns_data_product_type = array();
        }
        $have_selected_columns = isset( $selected_export_columns_setting['selected_columns'] ) && ! empty( $selected_export_columns_setting['selected_columns'] );
        $selected_columns = $have_selected_columns ? $selected_export_columns_setting['selected_columns'] : array();

        $this->selected_column_value_to_export = array_merge( $selected_columns, $columns_data_product_type );
        $this->selected_column_to_export = array_merge( $selected_columns, $columns_product_type );
        $this->column_name_in_excel = Fnt_ExcelExport::prepare_column_name_excel( $this->selected_column_to_export );
        $this->populate_product_data_to_worksheet();
    }

    private function proceed_populate_data_to_worksheet( PHPExcel &$objPHPExcel ) {
        $number_row = Fnt_ExcelDefineConstant::$num_row_populate_template + 1; // 1 row use for header
        try{
            $objPHPExcel->setActiveSheetIndexByName( Fnt_ExcelDefineConstant::$excel_data_sheet_name );
            $objWorkSheet = $objPHPExcel->getActiveSheet();

            $columns = $this->selected_column_value_to_export;

            $i = 1;
            // fill header
            foreach ( $columns as $column ) {
                // add attribute columns header
                if ( is_object( $column ) ) {
                    $column_excel = $this->column_name_in_excel[ $column->attribute_name ];
                    $excel_header_name = empty( $column->attribute_label ) ? $column->attribute_name : $column->attribute_label;
                    $objWorkSheet->setCellValue( $column_excel . $i, 'Attr_' . $excel_header_name );
                    $styleArray = Fnt_ExcelDefineConstant::$excel_header_row_style;
                    $objWorkSheet->getStyle( $column_excel . $i )->applyFromArray( $styleArray );
                    $objWorkSheet->getColumnDimension( $column_excel )->setWidth( max( strlen( 'Attr_' . $excel_header_name ), 25 ) );
                } else {
                    $column_excel = $this->column_name_in_excel[ $column ];
                    $objWorkSheet->setCellValue( $column_excel . $i, Fnt_ProductListCons::$column_table_display[ $column ] );
                    $styleArray = Fnt_ExcelDefineConstant::$excel_header_row_style;
                    $objWorkSheet->getStyle( $column_excel . $i )->applyFromArray( $styleArray );
                    $objWorkSheet->getColumnDimension( $column_excel )->setWidth( max( strlen( Fnt_ProductListCons::$column_table_display[ $column ] ), 25 ) );
                }
            }
            $range = null;
            $array_dropdown = array();
            // fill value
            for ( $i = 2; $i <= $number_row; $i++ ) {
                foreach ( $columns as $column ) {
                    // fill value for attribute columns
                    if ( is_object( $column ) ) {
                        $column_excel = $this->column_name_in_excel[ $column->attribute_name ];
                        switch ( $column->attribute_type ) {
                            case 'select':
                            case 'text':
                                Fnt_ExcelExport::validation_cell_dropdwonlist( $objPHPExcel, $column_excel, $i, $column->attribute_name, $array_dropdown );
                                break;
                        }
                    } else {
                        $column_excel = $this->column_name_in_excel[ $column ];
                        switch ( $column ) {
                            case Fnt_ProductListCons::COLUMN_ID:
                                Fnt_ExcelExport::validation_cell_number( $objWorkSheet, $column_excel, $i );
                                break;
                            case Fnt_ProductListCons::COLUMN_CATEGORIES:
                            case Fnt_ProductListCons::COLUMN_SHIPPING_CLASS:
                                Fnt_ExcelExport::validation_cell_dropdwonlist( $objPHPExcel, $column_excel, $i, $column, $array_dropdown );
                                break;
                            default:
                                if ( Fnt_ProductListCons::$column_format[$column] == 'int' || Fnt_ProductListCons::$column_format[$column] == 'float' ) {
                                    Fnt_ExcelExport::validation_cell_number($objWorkSheet, $column_excel, $i);
                                }
                                if ( array_key_exists( $column, Fnt_ProductListCons::$column_mapping ) ) {
                                    Fnt_ExcelExport::validation_cell_dropdwonlist( $objPHPExcel, $column_excel, $i, $column, $array_dropdown );
                                }
                                break;
                        }
                    }
                    $value = '';
                    $objWorkSheet->setCellValue( $column_excel . $i, $value );
                    $objWorkSheet->getStyle( $column_excel . $i )->getFont()->setSize( 12 );
                }
            }
            $objWorkSheet->freezePane( Fnt_ExcelDefineConstant::$freeze_pane );

            Fnt_ExcelExport::add_config_sheet( $objPHPExcel, $this->selected_column_to_export, $this->column_name_in_excel, $this->product_type, $this->attribute_columns );
        } catch ( Exception $ex ) {

        }
    }

    private function populate_product_data_to_worksheet() {
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator('VinaEcom')
                ->setLastModifiedBy('VinaEcom')
                ->setTitle(__('Exporting Products Template', 'fnt' ))
                ->setSubject('configuration')
                ->setDescription('configuration')
                ->setKeywords('configuration')
                ->setCategory('configuration');
            $ProductDataWorksheet = new PHPExcel_Worksheet($objPHPExcel);
            $ProductDataWorksheet->setTitle(Fnt_ExcelDefineConstant::$excel_data_sheet_name);
            $objPHPExcel->addSheet($ProductDataWorksheet);
            $this->proceed_populate_data_to_worksheet($objPHPExcel);

            if ( $objPHPExcel->sheetNameExists( 'Worksheet' ) ) {
                $objPHPExcel->setActiveSheetIndexByName( 'Worksheet' );
                $objPHPExcel->removeSheetByIndex( $objPHPExcel->getActiveSheetIndex() );
            }
            $objPHPExcel->setActiveSheetIndexByName( Fnt_ExcelDefineConstant::$excel_data_sheet_name );
            $file_name_export = 'export-template-'.$this->product_type.'-'.current_time( 'Y-m-d-H-m-s' );
            Fnt_ExcelExport::output_to_excel_file( $objPHPExcel, $file_name_export, '.xlsx', 'save_file' );
    }
}