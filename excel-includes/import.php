 <?php
require_once( FNT_DIR_EXCEL_INCLUDES . '/utils.php' );
class Fnt_ExcelImport extends Fnt_ExcelUtils {
    protected $allProductData = array();
    private $objPHPExcel = null;
    private $messages = array( 'row' => '' );
    private $column_headers;
    private $product_type = null;
    // check if excel file are export products from database
    private $is_excel_export_edit_file = false;
    private $row_count;
    private $row_count_false = 0;
    private $row_count_empty = 0;
    private $row_count_imported = 0;
    private $num_header_column = 0;
    private $num_header_column_in_config = 0;
    private $attribute;


    /**
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }
    public function __construct() {
        parent::__construct(
        );
    }
    /**
     * if dir does not exists, then make new dir
     * @param $upload_base_dir
     */
    private function make_dir_correctly( $upload_base_dir ) {
        if ( ! is_dir( $upload_base_dir) ) {
            mkdir( $upload_base_dir );
        }
    }
    /**
     * @param $fileName
     * @return string, content of file
     */
    public static function get_file_content( $fileName ) {
        $fp = fopen( $fileName, 'r' );
        if ( filesize( $fileName ) <= 0 ) {
            return '';
        }
        $content = fread( $fp, filesize( $fileName ) );
        fclose( $fp );
        return $content;
    }
    public static function get_import_error() {
        $array_error = Fnt_ProductImportExport::get_excel_import_error();
        if ( ! empty( $array_error ) && is_array( $array_error ) ) {
            return self::get_array_1D_from_multi_D( $array_error );
        }
        return array();
    }
    /**
     * 'D' is dimensional;
     * @param $array
     * @return array
     */
    private static function get_array_1D_from_multi_D( $array ) {
        $result = array();
        if ( ! empty( $array ) && is_array( $array ) ) {
            foreach ( $array as $item ) {
                if ( ! empty( $item ) && is_array( $item ) ) {
                    $result = array_merge( $result, self::get_array_1D_from_multi_D( $item ) );
                } else {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    /**
     * @param $import_file
     * @return string, dir to own file, include dir path and file name
     */
    private function move_to_upload_folder( $import_file ) {
        $this->make_dir_correctly( FNT_DIR_IMPORT );
        $fileName = FNT_DIR_IMPORT . '/' . $import_file['name'];
        Fnt_ProductImportExport::set_excel_import_file_name( $import_file['name'] );
        $tmpName = $import_file['tmp_name'];
        move_uploaded_file( $tmpName, $fileName );
        return $fileName;
    }
    public function proceed_importing_products( $import_file ) {
        WC_Product_Temp_Custom::delete_temp_data();
        $excel_file = $this->move_to_upload_folder($import_file);
        if (file_exists($excel_file)) {
            if(!$this->check_excel_file_is_2003_or_2007($excel_file)) {
                $this->messages[] = __('The extension excel file must be .xls or .xlsx .', 'fnt' );
                return false;
            }
            $inputFileType = PHPExcel_IOFactory::identify( $excel_file );
            $objReader = PHPExcel_IOFactory::createReader( $inputFileType );
            $objReader->setReadDataOnly( true );
            $this->objPHPExcel = $objReader->load( $excel_file );
            $sheet_names = $this->objPHPExcel->getSheetNames();

            if ( in_array( Fnt_ExcelDefineConstant::$excel_config_sheet_name, $sheet_names ) ) {
                $columns = $this->get_columns_config();
                $this->column_headers = $columns;
                $this->product_type = $this->get_product_type();
                if ( $this->product_type == null ) {
                    $this->is_excel_export_edit_file = true;
                }
            } else {
                $this->messages[] = __('File is lost some sheet need to import.', 'fnt' );
                return false;
            }

            if ( in_array( Fnt_ExcelDefineConstant::$excel_data_sheet_name, $sheet_names ) ) {
                $return = $this->get_columns_data();
            } else {
                $this->messages[] = __('File is invalid: lost data sheet.', 'fnt' );
                return false;
            }
        } else {
            $this->messages[] = __('File is not exists.', 'fnt' );
            return false;
        }
        if ( isset( $this->allProductData[ Fnt_ExcelDefineConstant::$excel_data_sheet_name] ) && ! empty( $this->allProductData[ Fnt_ExcelDefineConstant::$excel_data_sheet_name ] ) ) {
            $return = $this->insert_data_to_database() && $return;
        }
        if ( ! empty( $this->messages['row'] ) ) {
            $this->row_count_false += sizeof( $this->messages['row'] );
        }
        Fnt_ProductImportExport::set_excel_import_error( $this->messages );

        $error_link = sprintf( "<a target='_blank' href='%s'><strong>here</strong></a>", Fnt_QEPP::get_redirect_page_url( array( 'fnt_action' => 'show_import_excel_error' ) ) );
        $error_result_list = "We have $this->row_count row in excel file. Imported: $this->row_count_imported - Error: $this->row_count_false - Empty: $this->row_count_empty";
        $this->messages = array( "Have some error when import, click $error_link to view more result. <br /> $error_result_list" );
        return $return;
    }

    public static function get_all_product_ids() {
        global $wpdb;
        $sql = "SELECT post.ID
                FROM {$wpdb->posts} post
                WHERE post.post_type = 'product' AND post.post_status NOT IN ('auto-draft')";
        $products = $wpdb->get_results( $sql );
        if ( is_array( $products ) ) {
            foreach ( $products as $key => $product ) {
                $products[$key] = $product->ID;
            }
        } else {
            $products = array();
        }
        return $products;
    }
    private function insert_data_to_database() {
        $products = $this->allProductData[ Fnt_ExcelDefineConstant::$excel_data_sheet_name ];
        $return = true;
        $all_product_ids = self::get_all_product_ids();
        if ( $this->is_excel_export_edit_file ) {
            $products = array_reverse( $products );
        }
        $parent_id = 0;
        $variation_attribute_value = array();
        foreach ( $products as $product ) {
            /* insert new or edit */
            // if excel file have product id field, then we check some validation bellow
            if(isset($product[Fnt_ProductListCons::COLUMN_ID]) && !empty($product[Fnt_ProductListCons::COLUMN_ID]) && is_numeric($product[Fnt_ProductListCons::COLUMN_ID])) {
                $product_id = intval($product[Fnt_ProductListCons::COLUMN_ID]);
                if(!in_array($product_id, $all_product_ids) && $product_id > 0) {
                    $this->messages[] = __("In database don't have product have ID: ".$product_id, 'fnt' );
                    $this->row_count_false++;
                    $return = false;
                    continue;
                }
                if ( isset( $product[ Fnt_ProductListCons::COLUMN_SKU ] ) ) {
                    // check sku unique in wp database
                    if ( WC_Product_Custom::check_sku_exists( $product[ Fnt_ProductListCons::COLUMN_SKU ], 'product', $product_id ) ) {
                        $this->messages['row'][ $product['row'] ][] = sprintf( "Row %s: Column \"%s\" is duplicate in database.",
                                                                              $product['row'],
                                                                              Fnt_ProductListCons::$column_table_display[Fnt_ProductListCons::COLUMN_SKU] );
                        $return = false;
                        continue;
                    }
                }
            } else { // Add new
                if( isset( $product[ Fnt_ProductListCons::COLUMN_SKU ] ) ) {
                    // check sku unique in wp database
                    if ( WC_Product_Custom::check_sku_exists( $product[ Fnt_ProductListCons::COLUMN_SKU ], 'product' ) ) {
                        $this->messages['row'][ $product['row'] ][] = sprintf( "Row %s: Column \"%s\" is duplicate in database.",
                                                                              $product['row'],
                                                                              Fnt_ProductListCons::$column_table_display[Fnt_ProductListCons::COLUMN_SKU] );
                        $return = false;
                        continue;
                    }
                }
                // add attribute for product type variable
                if ( ! $this->is_excel_export_edit_file && $this->product_type == 'variable' ) {
                    $attributes = $this->get_attribute_in_config();
                    $product[ Fnt_ProductListCons::COLUMN_ATTRIBUTE ] = $attributes;
                }
            }
            if ( ! $this->is_excel_export_edit_file && $this->product_type == 'variable' ) {
                // product is parent product
                if ( isset( $product[ Fnt_ProductListCons::COLUMN_RELATIONSHIP ] )
                    && $product[ Fnt_ProductListCons::COLUMN_RELATIONSHIP ] == 'Parent' ) {
                    $parent_id = WC_Product_Temp_Custom::add_product( $product );
                    $variation_attribute_value = array(); // reset this to continue check attribute value of childrent items
                } else { // product is child product
                    if ( $parent_id === 0 || ( ! empty( $product['had_parent_column'] ) &&  $product['had_parent_column'] == 'no_parent' ) ) {
                        $this->messages['row'][ $product['row'] ][] = sprintf("Row %s: variation product is not have parent.", $product['row']);
                        $return = false;
                        continue;
                    }
                    if ( is_numeric( $parent_id ) ) {
                        $parent_id = absint( $parent_id );
                        $product[ Fnt_ProductListCons::COLUMN_VARIATION_DESCRIPTION ] = isset( $product[ Fnt_ProductListCons::COLUMN_CONTENT ] ) ? $product[ Fnt_ProductListCons::COLUMN_CONTENT ] : '';

                        $product_attributes = isset( $product[ Fnt_ProductListCons::COLUMN_ATTRIBUTE ] ) ? $product[ Fnt_ProductListCons::COLUMN_ATTRIBUTE ] : array();
                        $product_attributes_in_db = Fnt_Core::get_product_attributes();
                        $product_attribute_value = array();
                        $validate_attribute = true;
                        foreach ( $product as $key => $value ) {
                            if ( in_array( $key, $product_attributes ) ) {
                                if( ! array_key_exists ( $key, $product_attributes_in_db ) ) {
                                    $this->messages['row'][ $product['row'] ][] = sprintf( "Row %s: attributes is not in database.", $product['row'] );
                                    $return = false;
                                    $validate_attribute = false;
                                    continue;
                                }
                                // if correct attribute name, value can be empty, this mean the attribute can not choose
                                if( empty( $value ) ) {
                                    continue; // not check validation for this attribute
                                }
                                // if don't have attribute Name in DB, eject this row and add an message
                                if ( WC_Product_Variation_Custom::get_attribute_slug_by_name( wc_attribute_taxonomy_name( $key ), $value ) == null ) {
                                    $attribute_label = Fnt_Core::get_attribute_label( $key );
                                    $this->messages['row'][ $product['row'] ][] = sprintf( "Row %s: attributes $attribute_label don't have item \"$value\".", $product['row'] );
                                    $return = false;
                                    $validate_attribute = false;
                                    continue;
                                } else { // if correct format, prepare array content all attribute of this row
                                    $product_attribute_value[ $key ] = $value;
                                }
                            }
                        }
                        // if have incorrect of attribute value, eject this row
                        if ( ! $validate_attribute ) {
                            continue;
                        }
                        // check this attributes of row is exists yet
                        if ( in_array( $product_attribute_value, $variation_attribute_value ) ) {
                            $this->messages['row'][ $product['row'] ][] = "Row ".$product['row'].": product attributes already exists.";
                            $return = false;
                            continue;
                        } else {
                            $variation_attribute_value[] = $product_attribute_value;
                        }
                        WC_Product_Variation_Custom::add_variation( $parent_id, $product );
                        if ( $parent_id != 0 ) {
                            WC_Product_Variable::sync( $parent_id );
                            wc_delete_product_transients( $parent_id );
                        }
                    }
                }
            } else {
                WC_Product_Temp_Custom::add_product( $product );
            }
            $this->row_count_imported++;
        }
        return $return;
    }
    protected function get_columns_data() {
        $return = true;
        $this->objPHPExcel->setActiveSheetIndexByName( Fnt_ExcelDefineConstant::$excel_data_sheet_name );
        $objTemplateWorksheet = $this->objPHPExcel->getActiveSheet();
        $templateHighestRow = $objTemplateWorksheet->getHighestRow();
        $this->row_count = $templateHighestRow - 1;

        // todo: check this validation, it made user can't import at first release version
        // validation
//        $this->num_header_column = $objTemplateWorksheet->getHighestColumn();
//        $this->num_header_column = PHPExcel_Cell::columnIndexFromString($this->num_header_column);
//        if($this->num_header_column != $this->num_header_column_in_config) {
//            $this->messages[] = __("File are lost some column, num column header: ".$this->num_header_column.
//                " are different num header column we config: ".$this->num_header_column_in_config.
//                "Please check it, or export new template.", 'fnt' );
//            return false;
//        }

        $had_parent_column = false;
        $this->allProductData[ Fnt_ExcelDefineConstant::$excel_data_sheet_name ] = array();
        for ( $row = 2; $row <= $templateHighestRow; $row++ ) {
            $columns = array(
                'row' => ''
            );
            $row_empty = true;
            $row_validation = true;
            $columns['row'] = $row;
            foreach ( array_keys( $this->column_headers ) as $column ) {
                $cell_position = $this->column_headers[ $column ] . $row;
                $cell_value = $objTemplateWorksheet->getCell( $cell_position )->getCalculatedValue();
                $column_format = isset( Fnt_ProductListCons::$column_format[ $column ] ) ? Fnt_ProductListCons::$column_format[ $column ] : '';
                // if cell value have special character, ignore this row
                if ( in_array( strval( $cell_value ), Fnt_ExcelDefineConstant::$value_special ) ) {
                    $this->messages['row'][$row][] = sprintf( "Row $row, cell $cell_position: Column \"%s\" is wrong value.", Fnt_ProductListCons::$column_table_display[ $column ] );
                    $return = false;
                    $row_validation = false;
                }
                // validation in each row
                switch ( $column ) {
                    case Fnt_ProductListCons::COLUMN_TYPE:
                        if(!$this->is_excel_export_edit_file && (empty($cell_value)||$cell_value==null)) {
                            $this->messages['row'][$row][] = __("Don't have product type at row (".$row.") in excel file.", 'fnt' );
                            $return = false;
                            $row_validation = false;
                        }
                        $columns[ $column ] = $cell_value;
                        break;
                    case Fnt_ProductListCons::COLUMN_SKU:
                        // if sku empty, don't check
                        if ( empty( $cell_value ) ) {
                            $columns[ $column ] = $cell_value;
                            break;
                        }
                        // check sku unique in array
                        if($this->check_value_exists_in_array($this->allProductData[Fnt_ExcelDefineConstant::$excel_data_sheet_name], $column, $cell_value)) {
                            $this->messages['row'][$row][] = sprintf( "Row $row, cell $cell_position: Column \"%s\" is duplicate in excel file.", Fnt_ProductListCons::$column_table_display[ $column ] );
                            $return = false;
                            $row_validation = false;
                        }
                        $columns[$column] = $cell_value;
                        break;
                    case Fnt_ProductListCons::COLUMN_TAG:
                        if ( $cell_value != null ) {
                            $cell_value = explode( ',', $cell_value );
                            if ( is_array( $cell_value ) ) {
                                foreach ( $cell_value as $key => $value ) {
                                    $cell_value[ $key ] = trim( $value );
                                }
                            }
                        }
                        $columns[$column] = $cell_value;
                        break;
                    case Fnt_ProductListCons::COLUMN_SHIPPING_CLASS:
                        // remove space at behind and forward value
                        $cell_value = trim( $cell_value );
                        $columns[$column] = $cell_value;
                        break;
                    default:
                        // which column have mapping string in define constant
                        if ( isset( Fnt_ProductListCons::$column_mapping[ $column ] ) ) {
                            $mapping_exists = false;
                            // check value is in list defined?
                            foreach ( Fnt_ProductListCons::$column_mapping[ $column ] as $key => $value ) {
                                if ( $value == $cell_value ) {
                                    $columns[ $column ] = $key;
                                    $mapping_exists = true;
                                }
                            }
                            // if value is not in list defined, return messages error
                            if(!$mapping_exists && !empty($cell_value)) {
                                $this->messages['row'][$row][] = sprintf( "Row $row, cell $cell_position: Column \"%s\" value is not defined.", Fnt_ProductListCons::$column_table_display[ $column ] );
                                $return = false;
                                $row_validation = false;
                            }
                        }
                        // check validation if cell value type is number
                        if ( $cell_value != '' && ( $column_format == 'float' || $column_format == 'int' ) ) {
                            if ( $column_format == 'int' && is_numeric( $cell_value ) ) {
                                $cell_value = intval( $cell_value );
                            }
                            if ( ! is_numeric( $cell_value ) ) {
                                $this->messages['row'][$row][] = sprintf( "Row $row, cell $cell_position: Column \"%s\" is not correct format.", Fnt_ProductListCons::$column_table_display[ $column ] );
                                $return = false;
                                $row_validation = false;
                            }
                        }
                        $columns[ $column ] = $cell_value;
                        break;
                }
                if ( $cell_value != null ) {
                    $row_empty = false;
                }

                // set value default for cell type number if cell not have value, cell is null
                if ( empty( $cell_value ) && ( $column_format == 'float' || $column_format == 'int' ) ) {
                    $cell_value = 0;
                    $columns[ $column ] = $cell_value;
                }
            }
            if( isset( $columns[ Fnt_ProductListCons::COLUMN_RELATIONSHIP ] ) && $columns[ Fnt_ProductListCons::COLUMN_RELATIONSHIP ] == 'Parent' ) {
                if( ! $row_validation ) {
                    $had_parent_column = false;
                } else {
                    $had_parent_column = true;
                }
            }
            if ( ! $row_empty && $row_validation ) {
                // check have product type
                if ( ! $this->is_excel_export_edit_file ) {
                    global $wpdb;
                    $product_type = $this->product_type;
                    $wpdb->escape_by_ref( $product_type );
                    $columns[ Fnt_ProductListCons::COLUMN_TYPE ] = $product_type;
                }
                if( isset( $columns[ Fnt_ProductListCons::COLUMN_RELATIONSHIP ] ) && $columns[ Fnt_ProductListCons::COLUMN_RELATIONSHIP ] == 'Child' ) {
                    $columns['had_parent_column'] = $had_parent_column ? 'had_parent' : 'no_parent';
                }
                // check validation for cells value
                $this->allProductData[ Fnt_ExcelDefineConstant::$excel_data_sheet_name ][] = $columns;
            } elseif ( $row_empty ) {
                $this->row_count_empty++;
            }
        }
        return $return;
    }
    private function get_product_type() {
        $this->objPHPExcel->setActiveSheetIndexByName( Fnt_ExcelDefineConstant::$excel_config_sheet_name );
        $objTemplateWorksheet = $this->objPHPExcel->getActiveSheet();
        $product_type = $objTemplateWorksheet->getCell( Fnt_ExcelDefineConstant::$product_type_cell )->getCalculatedValue();
        return $product_type;
    }

    private function get_columns_config() {
        $columns = array();
        $this->objPHPExcel->setActiveSheetIndexByName( Fnt_ExcelDefineConstant::$excel_config_sheet_name );
        $objTemplateWorksheet = $this->objPHPExcel->getActiveSheet();
        $templateHighestRow = $objTemplateWorksheet->getHighestRow();
        $this->num_header_column_in_config = $templateHighestRow;
        for ( $row = 1; $row <= $templateHighestRow; $row++ ) {
            $column = $objTemplateWorksheet->getCell( Fnt_ExcelDefineConstant::$product_column_name_cell . $row )->getCalculatedValue();
            $column_excel = $objTemplateWorksheet->getCell( Fnt_ExcelDefineConstant::$product_column_excel_cell . $row )->getCalculatedValue();
            if ( empty( $column ) && empty( $column_excel ) ) {
                continue;
            }
            $columns[ $column ] = $column_excel;
        }
        return $columns;
    }
    private function get_attribute_in_config() {
        $attributes = array();
        $num_attributes = 0;
        $this->objPHPExcel->setActiveSheetIndexByName( Fnt_ExcelDefineConstant::$excel_config_sheet_name );
        $objTemplateWorksheet = $this->objPHPExcel->getActiveSheet();
        $num_attributes = $objTemplateWorksheet->getCell( Fnt_ExcelDefineConstant::$attribute_count_cell )->getCalculatedValue();
        for ( $row = 1; $row <= $num_attributes; $row++) {
            $attribute = $objTemplateWorksheet->getCell( Fnt_ExcelDefineConstant::$product_attribute_cell . $row )->getCalculatedValue();
            if ( empty( $attribute ) || $attribute == null ) {
                continue;
            }
            $attributes[] = $attribute;
        }
        return $attributes;
    }
    private function check_value_exists_in_array( $array, $key_in, $value_in ) {
        foreach ( $array as $item ) {
            foreach ( $item as $key => $value ) {
                if ( $key == $key_in && $value == $value_in ) {
                    return true;
                }
            }
        }
        return false;
    }
}
