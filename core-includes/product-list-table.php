<?php
    /*
     * Require file url-analyst.php
     * this file is content class, which will render query sentence for interactive with database
     */
    require_once FNT_DIR_CORE_INCLUDES . '/url-analyst.php';
    /*
     * This class is extends the WP_List_Table of wordpress
     * This use to display the table list
     * This class content functions extends parent class or write new(for do something)
     */
    class Fnt_CustomProductList extends WP_List_Table {
        // content all products data in current page
        private $products = array();
        private $columns_count;
        private $current_attributes = array();

        /**
         * Class constructor
         * @param array $args: since ver 1.1, add variable $args
         */
        public function __construct( $args = array() ) {
            parent::__construct( array(
                                     'singular' => __( 'Product', 'sp' ),
                                     'plural'   => __( 'Products', 'sp' ),
                                     'ajax'     => true, // since ver 1.1, use for send screen id to javascript variable
                                     'screen'   => isset( $args['screen'] ) ? $args['screen'] : null
                                 ) );
        }

        // This function use to show the message when don't have any product in list
        public function no_items() {
            _e( 'No products available.', 'fnt' );
        }

        /**
         * Function will return products in database as array by the query build by the param input
         * @param int $per_page, this is number of products will show in each page
         * @param int $page_number, this is the number of page will show
         * @return array, products will be get by the variable input
         */
        public function get_products( $per_page = LOADED_PRODUCTS_PER_PAGE, $page_number = 1 ) {
            global $wpdb;
            // build sql query to get products from database
            $sql = Fnt_Url_Handler::prepare_query( 'get_value', '', $per_page, $page_number );
            // get products by sql query by function get_results of global variable $wpdb
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            // define value return default
            $products = array();
            // check the $result must not empty and is an array
            if ( ! empty( $result ) && is_array( $result ) ) {
                // create a class factory, this class had custom base WC_Product_Factory
                $_pf = new WC_Product_Factory_Custom();
                foreach ( $result as $item ) {
                    // check product ID must be have and is a number
                    if ( ! empty( $item ) && isset( $item[ 'ID' ] ) && is_numeric( $item[ 'ID' ] ) ) {
                        // get the product by product ID
                        $product = $_pf->get_product( $item[ 'ID' ] );
                        // if exists product with this ID, put it to return value
                        if ( is_object( $product ) ) {
                            $products[] = $product;
                            // variable $this->products are use to localize script for edit inline
                            // function get_list_product_fields are get value need, and format to need format
                            $this->products[] = $product->get_list_product_fields( $this->get_columns() );
                        }
                    }
                }
            }

            return $products;
        }

        /**
         * Handles data query and filter, sorting, and pagination.
         */
        public function prepare_items() {
            // get number products per page in database
            $per_page = $this->get_items_per_page( 'products_per_page', LOADED_PRODUCTS_PER_PAGE );
            $current_page = $this->get_pagenum();
            $total_items = $this->record_count();

            // this function will show the navigate of paging
            $this->set_pagination_args( array(
                                            'total_items' => $total_items,
                                            // we have to calculate the total number of items
                                            'per_page' => $per_page
                                            // we have to determine how many items to show on a page
                                        ) );
            // get products will display in table
            $this->items = $this->get_products( $per_page, $current_page );
        }

        /**
         * Returns the number of records in database.
         * @return int
         */
        private function record_count() {
            global $wpdb;
            $sql = Fnt_Url_Handler::prepare_query();
            $num_record = $wpdb->get_var( $sql );
            if ( $num_record == null || ! is_numeric( $num_record ) ) {
                $num_record = 0;
            }
            return (int) $num_record;
        }

        /**
         * Count product by status input
         * Status 'all': will count all product not have status 'auto-draft', 'trash'
         * Another way is count product with status = $status but not in 'auto-draft'
         * @param string $status
         * @return int: number product
         */
        public function product_count( $status = 'all' ) {
            global $wpdb;
            $sql = Fnt_Url_Handler::product_count( $status );
            $num_product = $wpdb->get_var( $sql );
            if ( $num_product == null || ! is_numeric( $num_product ) ) {
                $num_product = 0;
            }
            return (int) $num_product;
        }

        /**
         * Define columns will be display in table columns
         * Key of each value of array will be use to get value field of each product to display
         * Value of each value of array will be display as title of each column
         * @return array
         */
        public function get_columns() {
            $columns = self::static_get_columns();
            $this->columns_count = is_array( $columns ) ? sizeof( $columns ) : 0;
            return $columns;
        }
        public static function static_get_columns() {
            $columns = array(
                'cb' => 'ID',
                Fnt_ProductListCons::COLUMN_ID                => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_ID ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_NAME              => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_NAME ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_CONTENT           => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_CONTENT ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_EXCERPT           => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_EXCERPT ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_TYPE              => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_TYPE ], 'fnt' ),
//                Fnt_ProductListCons::COLUMN_TYPE              => '<span class="wc-type tips" title="' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_TYPE ], 'fnt') . '">' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_TYPE ], 'fnt') . '</span>',
                Fnt_ProductListCons::COLUMN_THUMBNAIL         => '<span class="wc-image tips" title="' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_THUMBNAIL ], 'fnt') . '">' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_THUMBNAIL ], 'fnt') . '</span>',
                Fnt_ProductListCons::COLUMN_GALLERY           => '<span class="wc-gallery tips" title="' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_GALLERY ], 'fnt') . '">' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_GALLERY ], 'fnt') . '</span>',
                Fnt_ProductListCons::COLUMN_SKU               => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_SKU ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_CATEGORIES        => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_CATEGORIES ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_TAG               => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_TAG ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_STOCK             => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_STOCK ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_MANAGE_STOCK      => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_MANAGE_STOCK ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_STOCK_QUANTITY    => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_STOCK_QUANTITY ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_BACK_ORDERS       => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_BACK_ORDERS ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_SOLD_INDIVIDUALLY => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_SOLD_INDIVIDUALLY ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_REGULAR_PRICE     => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_REGULAR_PRICE ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_SALE_PRICE        => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_SALE_PRICE ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_FEATURE           => '<span class="wc-featured tips" title="' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_FEATURE ], 'fnt') . '">' . __(Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_FEATURE ], 'fnt') . '</span>',
                Fnt_ProductListCons::COLUMN_DATE              => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_DATE ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_SLUG              => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_SLUG ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_WEIGHT            => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_WEIGHT ] . " (" . Fnt_Core::get_weight_unit() . ")", 'fnt' ),
                Fnt_ProductListCons::COLUMN_LENGTH            => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_LENGTH ] . " (" . Fnt_Core::get_dimension_unit() . ")", 'fnt' ),
                Fnt_ProductListCons::COLUMN_WIDTH             => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_WIDTH ] . " (" . Fnt_Core::get_dimension_unit() . ")", 'fnt' ),
                Fnt_ProductListCons::COLUMN_HEIGHT            => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_HEIGHT ] . " (" . Fnt_Core::get_dimension_unit() . ")", 'fnt' ),
                Fnt_ProductListCons::COLUMN_ALLOW_COMMENTS    => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_ALLOW_COMMENTS ], 'fnt' ),
                Fnt_ProductListCons::COLUMN_STATUS            => __( Fnt_ProductListCons::$column_table_display[ Fnt_ProductListCons::COLUMN_STATUS ], 'fnt' )
            );

            return $columns;
        }

        /**
         * Define columns can be short
         * @return array
         */
        public function get_sortable_columns() {
            // don't allow sort if don't have any items
            if ( ! $this->has_items() ) {
                return array();
            }
            // allow sort with bellow columns
            $sortable_columns = array(
                Fnt_ProductListCons::COLUMN_ID             => array( Fnt_ProductListCons::COLUMN_ID, false ),
                Fnt_ProductListCons::COLUMN_NAME           => array( Fnt_ProductListCons::COLUMN_NAME, false ),
                Fnt_ProductListCons::COLUMN_SKU            => array( Fnt_ProductListCons::COLUMN_SKU, false ),
                Fnt_ProductListCons::COLUMN_STOCK          => array( Fnt_ProductListCons::COLUMN_STOCK, false ),
                Fnt_ProductListCons::COLUMN_STOCK_QUANTITY => array( Fnt_ProductListCons::COLUMN_STOCK_QUANTITY, false ),
                Fnt_ProductListCons::COLUMN_SALE_PRICE     => array( Fnt_ProductListCons::COLUMN_SALE_PRICE, false ),
                Fnt_ProductListCons::COLUMN_REGULAR_PRICE  => array( Fnt_ProductListCons::COLUMN_REGULAR_PRICE, false ),
                Fnt_ProductListCons::COLUMN_FEATURE        => array( Fnt_ProductListCons::COLUMN_FEATURE, false ),
                Fnt_ProductListCons::COLUMN_DATE           => array( Fnt_ProductListCons::COLUMN_DATE, false ),
                Fnt_ProductListCons::COLUMN_SLUG           => array( Fnt_ProductListCons::COLUMN_SLUG, false ),
                Fnt_ProductListCons::COLUMN_PRIVATE        => array( Fnt_ProductListCons::COLUMN_PRIVATE, false ),
                Fnt_ProductListCons::COLUMN_TAX_STATUS     => array( Fnt_ProductListCons::COLUMN_TAX_STATUS, false ),
                Fnt_ProductListCons::COLUMN_TAX_CLASS      => array( Fnt_ProductListCons::COLUMN_TAX_CLASS, false ),
                Fnt_ProductListCons::COLUMN_WEIGHT         => array( Fnt_ProductListCons::COLUMN_WEIGHT, false ),
                Fnt_ProductListCons::COLUMN_LENGTH         => array( Fnt_ProductListCons::COLUMN_LENGTH, false ),
                Fnt_ProductListCons::COLUMN_WIDTH          => array( Fnt_ProductListCons::COLUMN_WIDTH, false ),
                Fnt_ProductListCons::COLUMN_HEIGHT         => array( Fnt_ProductListCons::COLUMN_HEIGHT, false ),
                Fnt_ProductListCons::COLUMN_BACK_ORDERS    => array( Fnt_ProductListCons::COLUMN_BACK_ORDERS, false ),
                Fnt_ProductListCons::COLUMN_ORDER          => array( Fnt_ProductListCons::COLUMN_ORDER, false ),
                Fnt_ProductListCons::COLUMN_ALLOW_COMMENTS => array( Fnt_ProductListCons::COLUMN_ALLOW_COMMENTS, false ),
                Fnt_ProductListCons::COLUMN_STATUS         => array( Fnt_ProductListCons::COLUMN_STATUS, false )
            );

            return $sortable_columns;
        }

        /**
         * Render a column when specific method exist.
         * @param $item, product object
         * @return string, this will be set to table cell
         * $value_hidden: this value content in hidden field, use it for edit field
         * $column_value: this value is display in list product
         */
        public function column_cb( $item ) {
            $product_id = isset( $item->id ) ? $item->id : '';
            $extra_attr = '';
            if ( $product_id != $item->get_product_id() ) {
                $extra_attr = " old-product-id = '{$item->get_product_id()}' ";
            }
            $cb = "<input type='checkbox' class='product-single-checkbox-row' $extra_attr data-product-id='$product_id' data-product-row-id='row-$product_id' name='bulk-delete[]' value='$product_id' />";
            $hidden_fields = "<input type='hidden' name='products[$product_id][is-modifying]' class='input-is-modifying' value='no' />";

            return $cb . $hidden_fields;
        }

        /**
         * Render cell product name and row actions: view, edit, duplicate
         * @param $item
         * @return string
         */
        private function get_row_actions( $item ) {
            if ( ! isset( $item->id ) ) {
                return '';
            }
            // add link action for each product
            // action link doesn't display for product in trash
            if ( Fnt_Url_Handler::is_in_trash_filter() ) {
                $actions = array();
            } else {
                // get link to view this product
                $action_view = get_permalink( $item->id );
                // get link to edit this product
                $action_edit = get_edit_post_link( $item->id );
                // add flag to check is page admin in iframe popup
                $action_edit.= '&amp;fnt_iframe_popup=1';
                // get link to duplicate product
                $action_duplicate = wp_nonce_url( admin_url( 'edit.php?post_type=product&action=duplicate_product&amp;post=' . $item->id ),
                                                  'woocommerce-duplicate-product_' . $item->id );
                $edit_link = sprintf( '<a href="#" link="%s" title="Edit product" product-id="%s" product-type="%s" data-product-row-id="%s" class="fnt-edit-product">%s</a>', $action_edit, $item->id, $item->product_type, 'row-' . $item->id, __('Edit', 'fnt' ) );
                $actions = array(
                    'view'      => sprintf( "<a href='%s' target='_blank'>%s</a>", $action_view, __('View', 'fnt' ) ),
                    'edit'      => $edit_link,
                    'duplicate' => sprintf( "<a href='%s'>%s</a>", $action_duplicate, __('Duplicate', 'fnt' ) ),
                );
            }

            return $this->row_actions( $actions );
        }
        /**
         * Render a column when no column specific method exist.
         * @param array $item
         * @param string $column_name
         * @return string
         * $value_hidden: this value content in hidden field, use it for edit inline
         * $column_value: this value is display in product list table
         */
        public function column_default( $item, $column_name ) {
            $result = self::render_table_cell_value( $item, $column_name );
            // if column is Post title, add row actions
            if ( $column_name == Fnt_ProductListCons::COLUMN_NAME ) {
                $result .= $this->get_row_actions( $item );
            }
            return $result;
        }

        /*
         * Function use render table cell value, include html tags
         */
        public static function render_table_cell_value( $item, $column_name ) {
            // not render for column checkbox, it have itself function to render value
            // or if $item is not an object
            if ( $column_name == 'cb' || ! is_object( $item ) ) {
                return '';
            }
            $value_hidden = '';
            switch ( $column_name ) {
                case Fnt_ProductListCons::COLUMN_ID:
                    $column_value = $item->get_product_id();
                    break;
                case Fnt_ProductListCons::COLUMN_EXCERPT:
                    $column_value = $item->get_product_excerpt();
                    break;
                case Fnt_ProductListCons::COLUMN_CONTENT:
                    $column_value = $item->get_product_content();
                    break;
                case Fnt_ProductListCons::COLUMN_THUMBNAIL:
                    $column_value = $item->get_image_thumbnail( array( 40, 40 ) );
                    break;
                case Fnt_ProductListCons::COLUMN_GALLERY:
                    $column_value = $item->get_image_gallery();
                    break;
                case Fnt_ProductListCons::COLUMN_TYPE:
                    $column_value = $item->get_product_type();
                    break;
                case Fnt_ProductListCons::COLUMN_FEATURE:
                    $column_value = $item->get_product_featured();
                    break;
                case Fnt_ProductListCons::COLUMN_CATEGORIES:
                case Fnt_ProductListCons::COLUMN_TAG:
                    $value_hidden = $item->get_product_list_term_meta( $column_name, 'term_id' );
                    $column_value = $item->get_product_list_term_meta( $column_name, 'name' );
                    break;
                case Fnt_ProductListCons::COLUMN_SALE_PRICE:
                case Fnt_ProductListCons::COLUMN_REGULAR_PRICE:
                    $price = $item->$column_name;
                    $value_hidden = $price;
                    $column_value = $item->get_formatted_product_price( $price );
                    break;
                case Fnt_ProductListCons::COLUMN_STOCK_QUANTITY:
                    $column_value = $item->$column_name;
                    $column_value = intval( $column_value );
                    $value_hidden = $column_value;
                    break;
                case Fnt_ProductListCons::COLUMN_ATTRIBUTE:
                    $column_value = $item->get_selection_variation_attributes();
                    $value_hidden = '';
                    break;
                default:
                    // get column name with out prefix '_' for get value in product object
                    $column_name_in_object = trim( Fnt_ProductListCons::$column_name_in_db[ $column_name ], '_' );
                    // we have data in 2 table: post and post_meta, so need 2 way to get it
                    // if value in table post, get it follow way bellow
                    if ( Fnt_ProductListCons::$column_table_in_db[ $column_name ] == 'posts' ) {
                        $value_hidden = $column_value = $item->get_post_data()->$column_name_in_object;
                    } else { // if value in table post_meta, get it
                        $value_hidden = $column_value = $item->$column_name_in_object;
                    }
                    // when column is checkbox
                    // check this value is check value or uncheck value
                    // and render the checkbox
                    if ( Fnt_ProductListCons::$product_type_mapping[ $column_name ] == 'checkbox' ) {
                        // Set the internal pointer of an array to its first element
                        reset( Fnt_ProductListCons::$column_mapping[ $column_name ] );
                        // get first key in array, default it is the value in uncheck
                        $value_when_unchecked = key( Fnt_ProductListCons::$column_mapping[ $column_name ] );
                        if ( ! empty( $value_when_unchecked ) ) {
                            $column_value = $item->get_check_box( $column_value, $value_when_unchecked );
                        }
                    }
            }
            $result = self::get_column_final_value( $item, $column_name, $column_value, $value_hidden );
            return $result;
        }

        /**
         *
         * @param $item
         * @param $column_name
         * @param $column_value
         * @param $value_hidden
         * @return string
         */
        private static function get_column_final_value( $item, $column_name, $column_value, $value_hidden ) {
            $div_attr = '';
            $div_classes = 'ajax-replace ';
            $span_classes = '';
            // get value mapping
            if ( $column_name != Fnt_ProductListCons::COLUMN_TYPE && isset( Fnt_ProductListCons::$column_mapping[ $column_name ][ $column_value ] ) ) {
                $column_value = Fnt_ProductListCons::$column_mapping[ $column_name ][ $column_value ];
            }
            // content value with span tag
            if ( isset( Fnt_ProductListCons::$column_format[ $column_name ] ) && ( Fnt_ProductListCons::$column_format[ $column_name ] == 'float' || Fnt_ProductListCons::$column_format[ $column_name ] == 'int' ) ) {
                $span_classes .= ' input-number';
            }
            $column_value = "<span class='$span_classes input-text'>$column_value</span>";
            // get hidden value
            $args = array( 'product_id'   => $item->id,
                'value_hidden' => $value_hidden
            );
            $hidden = self::create_input_hidden_by_type( $column_name, $args );
            // prepare final value
            if ( in_array( $column_name, Fnt_ProductListCons::$only_view_columns ) ) {
                if ( Fnt_ProductListCons::$product_type_mapping[ $column_name ] == 'checkbox' ) {
                    return '';
                }
            } else {
                if ( in_array( $column_name, self::get_columns_for_product_type( $item ) ) ) {
                    switch ( $column_name ) {
                        case Fnt_ProductListCons::COLUMN_SALE_PRICE:
                            if ( ! $item->price_validation() ) {
                                $div_classes .= ' background-validate-sale-price ';
                            }
                            break;
                        case Fnt_ProductListCons::COLUMN_NAME:
                            if ( ! Fnt_Url_Handler::is_in_trash_filter() ) {
                                $div_classes .= ' column-product-name ';
                            }
                            break;
                    }
                    // add extra class to alert to user if value of cell is miss some validate @nhan
                    $column_format = ! empty( Fnt_ProductListCons::$column_format[ $column_name ] ) ? Fnt_ProductListCons::$column_format[ $column_name ] : '';
                    if ( $column_format == 'int' || $column_format == 'float' ) {
                        try {
                            // if value is number
                            // get column name with out prefix '_' for get value in product object
                            $column_name_in_object = trim( Fnt_ProductListCons::$column_name_in_db[ $column_name ], '_' );
                            // we have data in 2 table: post and post_meta, so need 2 way to get it
                            // if value in table post, get it follow way bellow
                            if ( Fnt_ProductListCons::$column_table_in_db[ $column_name ] == 'posts' ) {
                                $column_real_value = $item->get_post_data()->$column_name_in_object;
                            } else { // if value in table post_meta, get it
                                $column_real_value = $item->$column_name_in_object;
                            }
                            // and value is small than 0, add class to change color to user know
                            if ( $column_real_value < 0 ) {
                                $div_classes .= ' cell-alert-color ';
                            }
                        } catch( Exception $ex ) {}
                    }
                    // add extra class @van
                    if ( Fnt_ProductListCons::$product_type_mapping[ $column_name ] != 'checkbox' && $column_name != Fnt_ProductListCons::COLUMN_CATEGORIES ) {
                        $div_classes .= ' cell-edit-inline ';
                    }
                } else { // if column product not support edit, display to user see, since 1.1
                    if ( $item->product_type == 'variable' ) {
                        switch($column_name) {
                            case Fnt_ProductListCons::COLUMN_SALE_PRICE:
                                $column_value  = 'Min: ' . $item->get_formatted_product_price( $item->get_variation_sale_price('min') );
                                $column_value .= '<br />';
                                $column_value .= 'Max: ' . $item->get_formatted_product_price( $item->get_variation_sale_price('max') );
                                break;
                            case Fnt_ProductListCons::COLUMN_REGULAR_PRICE:
                                $column_value  = 'Min: ' . $item->get_formatted_product_price( $item->get_variation_regular_price('min') );
                                $column_value .= '<br />';
                                $column_value .= 'Max: ' . $item->get_formatted_product_price( $item->get_variation_regular_price('max') );
                                break;
                            default:
                                $column_value = 'Not editable';
                        }
                        $hidden = '';
                    } else {
                        $column_value = 'Not editable';
                        $hidden = '';
                    }
                }
            }

            return "<div $div_attr class='$div_classes wrap-input-text bootstrap-wrapper $column_name' data-product-field-name='$column_name'>"
            . $column_value
            . $hidden
            . '</div>';
        }

        /**
         * Make input hidden to edit products inline
         * @param $column_name, column to create value hidden
         * @param array $args
         * @return mixed|string
         */
        public static function create_input_hidden_by_type( $column_name, $args = array() ) {
            $column_name_in_db = isset( Fnt_ProductListCons::$column_name_in_db[ $column_name ] ) ? Fnt_ProductListCons::$column_name_in_db[ $column_name ] : '';
            switch ( Fnt_ProductListCons::$product_type_mapping[ $column_name ] ) {
                case 'input':
                    $input_center = ' input-center';
                    if ( $column_name == Fnt_ProductListCons::COLUMN_NAME ) {
                        if ( Fnt_Url_Handler::is_in_trash_filter() ) {
                            $input_center = 'input-center';
                        } else {
                            $input_center = '';
                        }
                    }
                    if ( in_array( $column_name, Fnt_ProductListCons::$input_numbers ) ) {
                        if ( in_array( $column_name, Fnt_ProductListCons::$validate_price ) ) {
                            $classes = 'input-money validate-price';
                        } else {
                            $classes = 'input-numbers';
                        }
                    } else {
                        if ( in_array( $column_name, Fnt_ProductListCons::$input_only_numbers ) ) {
                            $classes = 'input-only-numbers';
                        } else {
                            $classes = '';
                        }
                    }
                    $str_input = "<input type='text' data-product-filed-name='$column_name' value=\"{$args['value_hidden']}\" class='input-text-editable hidden $classes $input_center' />";
                    break;
                case 'product-tag':
                    $str_input = sprintf("
                            <div class='wrapper-product-tags hidden'>
                                <textarea data-product-filed-name='$column_name' class='product-tags input-text-editable'>{$args['value_hidden']}</textarea>
                                <a href='#' class='btn-push-tags btn btn-xs btn-primary'>%s</a>
                            <div>", __('OK', 'fnt'));
                    break;
                case 'dropdown-list':
                    $str_input = "<select name='products[{$args['product_id']}][$column_name_in_db]' class='$column_name input-text-editable hidden input-center'>";
                    foreach ( Fnt_ProductListCons::$column_mapping[ $column_name ] as $key => $value ) {
                        $str_input .= "<option value='$key'>$value</option>";
                    }
                    $str_input .= '</select>';
                    break;
//                case 'product-cat':
//                    $product_cat_id = 'product_cat_' . $args['product_id'];
//                    $args = array(
//                        'show_option_all'    => __( 'Select a category', 'fnt' ),
//                        'orderby'            => 'name',
//                        'order'              => 'ASC',
//                        'show_count'         => 0,
//                        'hide_empty'         => 0,
//                        'child_of'           => 0 ,
//                        'exclude'            => '',
//                        'echo'               => 0,
//                        'selected'           => 0,
//                        'hierarchical'       => 1,
//                        'name'               => 'product_cat',
//                        'id'                 => $product_cat_id,
//                        'class'              => 'product_cat input-text-editable hidden',
//                        'depth'              => 0,
//                        'tab_index'          => 0,
//                        'taxonomy'           => 'product_cat',
//                        'hide_if_empty'      => false,
//                        'value_field'        => 'term_id'
//                    );
//                    $str_input = wp_dropdown_categories( $args );
//                    // make multi select
//                    $str_input = str_replace( 'id=', 'multiple="multiple" id=', $str_input );
//                    $str_input = self::get_categories_selection( $args['product_id'] );
//                    break;
                default:
                    $str_input = '';
            }

            return $str_input;
        }

        /**
         * This is render the nav of product type, in top left of table
         * @see WP_List_Table::get_views()
         * @return array: $views
         */
        public function get_views() {
            $current_class = isset( $_GET[ 's' ] ) ? '' : ' class="current"';
            $views = array();
            if ( isset( Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_STATUS ] ) ) {
                $column = Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_STATUS ];

                $current = ( ! empty( $_GET[ $column ] ) ? $_GET[ $column ] : 'all' );

                $class = $current == 'all' ? $current_class : '';
                $all_url = Fnt_QEPP::get_redirect_page_url();
                $item_count = $this->product_count();
                $views[ 'all' ] = sprintf("<a href='$all_url' $class>%s <span class='count'>($item_count)</span></a>", __( 'All', 'fnt' ));

                if ( isset( Fnt_ProductListCons::$column_mapping[ Fnt_ProductListCons::COLUMN_STATUS ] ) ) {
                    $status_array = Fnt_ProductListCons::$column_mapping[ Fnt_ProductListCons::COLUMN_STATUS ];

                    if ( ! empty( $status_array ) && is_array( $status_array ) ) {
                        foreach ( $status_array as $key => $value ) {
                            $foo_url = Fnt_QEPP::get_redirect_page_url( array( $column => $key ) );
                            $item_count = $this->product_count( $key );
                            $class = $current == $key ? ' class="current"' : '';
                            $views[ $key ] = sprintf( "<a href='$foo_url' $class>%s  <span class='count'>($item_count)</span></a>", __( $value, 'fnt' ) );
                        }
                    }
                }
            }

            return $views;
        }

        /**
         * Generate the table navigation above or below the table
         * @since 3.1.0
         * @access protected
         * @param string $which
         */
        public function display_tablenav( $which ) {
            if ( 'top' == $which ) {
                wp_nonce_field( 'bulk-' . $this->_args[ 'plural' ] );
            }
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                ?>
                <br class="clear"/>
            </div>
            <?php
        }

        /**
         * Add extra markup in the toolbars before or after the list
         * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
         */
        public function extra_tablenav( $which ) {
            if ( $which == 'top' ) {
                $this->get_product_cta();
                $this->get_filter_form();
            }
            if ( $which == 'bottom' ) {
                echo '';
            }
        }

        public static function get_categories_selection( $product_id = 0 ) {
            $result = '
            <div class="bootstrap-wrapper popup-select-product-type-wrapper hidden">
                <div class="modal">
                    <div class="modal-backdrop popup-categories-click-out"></div>
                    <div class="modal-dialog popup-categories-click-out">
                        <div class="modal-content">
                            <div class="arrow-left"></div>
                            <div class="arrow-right"></div>
                            <div class="modal-header">
                                <div class="cat-search">
                                    <input type="text" class="cat-search" placeholder="' . __( 'Search', 'fnt' ) . '" />
                                    <button type="button" class="button button-first-init button-select-cat" value1="' . __( 'Checked', 'fnt' ) . '" value2="' . __( 'Show all', 'fnt' ) .'">'. __( 'Checked', 'fnt' ) . '</button>
                                </div>
                            </div>
                            <div class="modal-body">
                                ' . WC_Product_Custom::get_cat_selection( $product_id ) . '
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="button close-selection-cat">' . __( 'Cancel', 'fnt' ) .'</button>
                                <button type="button" class="button button-primary save-selection-cat">' . __( 'Save', 'fnt' ) . '</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ';
            return $result;
        }

        /*
         * get button CTA(call to action) for do action of table product
         */
        private function get_product_cta() {
            $button_classes = 'fnt-button-cta';
            if ( ! $this->has_items() ) {
                $button_had_disabled = ' button-disabled';
            } else {
                $button_had_disabled = '';
            }
            $style = '';
            ?>
            <div class="alignleft actions action-button" <?php echo $style;?>>
                <button type="button" disabled="disabled" class="button <?php echo $button_classes . $button_had_disabled; ?>" id="button-delete-product" >
                    <?php echo __( 'Delete', 'fnt' ); ?>
                </button>
                <?php
                    if ( Fnt_Url_Handler::is_in_trash_filter() ) { ?>
                        <button type="button" disabled="disabled" class="button <?php echo $button_classes . $button_had_disabled; ?>"
                                id="button-restore-product"><?php echo __( 'Restore', 'fnt' ); ?>
                        </button>
                        <?php
                    } else {
                        ?>
                        <button type="button" disabled="disabled" class="button <?php echo $button_classes . $button_had_disabled; ?>"
                                id="button-move-product-to-trash"><?php echo __( 'Trash', 'fnt' ); ?>
                        </button>
                        <?php
                    }
                ?>
            </div>
            <?php
        }

        /**
         * Get form change product types
         */
        private function get_product_types() {
            ?>
            <div class="alignleft actions product-type-change">
                <?php
                    // Get dropdown Product types
                    $column_type = Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ];
                    $args = array(
                        'show_option_all' => __( 'Change to', 'fnt' ),
                        'orderby'         => 'name',
                        'hide_empty'      => 0,
//                        'selected'        => Fnt_Url_Handler::get_current_product_type_change_to(),
                        'hierarchical'    => 1,
                        'id'              => 'change_product_type',
                        'name'            => 'change_product_type',
                        'class'           => 'change_product_type',
                        'taxonomy'        => $column_type,
                    );
                    wp_dropdown_categories( $args );
                ?>
                <input type="button" disabled="disabled"  name="change-product-type" id="change-product-type" class="button fnt-button-cta" value="<?php echo __('Change', 'fnt' ); ?>"/>
            </div>
            <?php
        }

        /*
         * Get form filter
         */
        private function get_filter_form() {
            ?>
            <div class="alignleft actions filter-form">
                <?php
                    // Get months dropdown
                    $this->months_dropdown( Fnt_Url_Handler::get_product_type() );
                    // Get Categories dropdown
                    $column_category = Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_CATEGORIES ];
                    $args = array(
                        'show_option_all'   => __( 'Select a category', 'fnt' ),
                        'show_option_none'  => __( 'Uncategorized', 'fnt' ),
                        'option_none_value' => OPTION_NONE_VALUE,
                        'orderby'           => 'name',
                        'hide_empty'        => true,
                        'selected'          => Fnt_Url_Handler::get_current_term_id( $column_category ),
                        'hierarchical'      => 1,
                        'id'                => 'product_cat_filter',
                        'name'              => $column_category,
                        'class'             => $column_category,
                        'taxonomy'          => $column_category,
                        'hide_if_empty'     => 1
                    );
                    wp_dropdown_categories( $args );
                    // Get dropdown Product types
                    $column_type = Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ];
                    $args = array(
                        'show_option_all' => __( 'All product types', 'fnt' ),
                        'orderby'         => 'name',
                        'hide_empty'      => 0,
                        'selected'        => Fnt_Url_Handler::get_current_term_id( $column_type ),
                        'hierarchical'    => 1,
                        'name'            => $column_type,
                        'class'           => $column_type,
                        'taxonomy'        => $column_type,
                    );
                    wp_dropdown_categories( $args );
                ?>
                <input type="submit" name="filter-action" id="post-query-submit" class="button" value="<?php echo __('Filter', 'fnt' ); ?>"/>
            </div>
            <?php
        }

        /*
         * this use to know will edit in what column of each product
         * Get columns of product type from list define
         */
        public static function get_columns_for_product_type( $item ) {
            $product_type = $item->get_type();
            $columns_for_product_type = Fnt_ProductListCons::$column_defaults;
            if ( isset( Fnt_ProductListCons::$column_product_type[ $product_type ] ) ) {
                $columns_for_product_type = array_merge( Fnt_ProductListCons::$column_product_type[ $product_type ], $columns_for_product_type );
            }

            return $columns_for_product_type;
        }

        /**
         * Generates content for a single row of the table
         *
         * @access public
         *
         * @param object $item The current item
         */
        public function single_row( $item ) {
            static $alternate;
            $product_id = $item->id;
            $class_color = '';
            $attr = 'product-id="'.$product_id.'"';
            $attr .= ' product-type="'.$item->product_type.'"';
            // check if this product is just add
            if ( $item->is_just_add() ) {
                // get color in option database
                $just_add_color = Fnt_Core::get_just_add_color();
                // if this option is not define in database, add class default
                if ( empty( $just_add_color ) ) {
                    $class_color .= 'adding-row-color';
                } else { // if have option, set color in style for row
                    $attr .= " style='background-color: {$just_add_color}!important;'";
                }
            }
            $alternate = 'alternate' == $alternate ? '' : 'alternate';
            echo "<tr class='main-row-$product_id $class_color $alternate' $attr>";
            $this->single_row_columns( $item );
            echo '</tr>';
        }

        /*
         * get data of list product for localize script to use for edit inline
         */
        public function get_full_product_data() {
            $results = array();
            if ( ! empty( $this->products ) && is_array( $this->products ) ) {
                foreach ( $this->products as $item_value ) {
                    if ( isset( $item_value[ Fnt_ProductListCons::COLUMN_TYPE ] ) ) {
                        if ( ! empty( $item_value[ Fnt_ProductListCons::COLUMN_TYPE ] ) ) {
                            $results[] = $this->get_product_meta_to_array( $item_value );
                        }
                    }
                }
            }

            // since ver 1.1
            $results = self::get_final_product_data_to_js( $results );

            return $results;
        }



        /**
         * Since ver 1.1
         * This function use for make final value to localize to javascript
         * @param $products
         * @return array
         */
        public static function get_final_product_data_to_js( $products ) {
            $product_data = array();
            if ( ! empty( $products ) ) {
                foreach ( $products as $item ) {
                    $item = (is_object($item)) ? get_object_vars($item) : $item;
                    if(isset($item[Fnt_ProductListCons::COLUMN_GALLERY]) && is_array($item[Fnt_ProductListCons::COLUMN_GALLERY])){
                        $item[Fnt_ProductListCons::COLUMN_GALLERY] = implode(',',$item[Fnt_ProductListCons::COLUMN_GALLERY]);
                    }
                    $product_data['row-' . $item[Fnt_ProductListCons::COLUMN_ID]] = $item;
                }
            }
            return $product_data;
        }

        private function get_product_meta_to_array( $product = array() ) {
            $result = self::get_product_meta_to_array_static( $product );
            return $result;
        }

        public static function get_product_meta_to_array_static( $product = array() ) {
            $result = array();
            $fields = array();
            // if have product fields default for all product type
            if ( isset( Fnt_ProductListCons::$product_mapping_js[ 'default' ] ) ) {
                $fields_default = Fnt_ProductListCons::$product_mapping_js[ 'default' ];
                // check fields default is exists and is an array
                if ( ! empty( $fields_default ) && is_array( $fields_default ) ) {
                    // if product have product type
                    if ( isset( $product[ Fnt_ProductListCons::COLUMN_TYPE ] ) ) {
                        $product_type = $product[ Fnt_ProductListCons::COLUMN_TYPE ];
                        switch ( $product_type ) {
                            case 'simple':
                                // if have product fields for product type simple
                                if ( isset( Fnt_ProductListCons::$product_mapping_js[ 'simple' ] ) ) {
                                    $fields_simple = Fnt_ProductListCons::$product_mapping_js[ 'simple' ];
                                    // check fields for this product type is not empty and is an array
                                    if ( ! empty( $fields_simple ) && is_array( $fields_simple ) ) {
                                        $fields = array_merge( $fields_default, $fields_simple );
                                    }
                                }
                                break;
                            case 'grouped':
                                if ( isset( Fnt_ProductListCons::$product_mapping_js[ 'grouped' ] ) ) {
                                    $fields_grouped = Fnt_ProductListCons::$product_mapping_js[ 'grouped' ];
                                    if ( ! empty( $fields_grouped ) && is_array( $fields_grouped ) ) {
                                        $fields = array_merge( $fields_default, $fields_grouped );
                                    }
                                }
                                break;
                            case 'external':
                                if ( isset( Fnt_ProductListCons::$product_mapping_js[ 'external' ] ) ) {
                                    $fields_external = Fnt_ProductListCons::$product_mapping_js[ 'external' ];
                                    if ( ! empty( $fields_external ) && is_array( $fields_external ) ) {
                                        $fields = array_merge( $fields_default, $fields_external );
                                    }
                                }
                                break;
                            case 'variable':
                                if ( isset( Fnt_ProductListCons::$product_mapping_js[ 'variable' ] ) ) {
                                    $fields_variable = Fnt_ProductListCons::$product_mapping_js[ 'variable' ];
                                    if ( ! empty( $fields_variable ) && is_array( $fields_variable ) ) {
                                        $fields = array_merge( $fields_default, $fields_variable );
                                    }
                                }
                                break;
                            // since ver 1.1
                            // get fields for variation product
                            case 'variation':
                                if ( isset( Fnt_ProductListCons::$product_mapping_js[ 'variation' ] ) ) {
                                    $fields_variation = Fnt_ProductListCons::$product_mapping_js[ 'variation' ];
                                    if ( ! empty( $fields_variation ) && is_array( $fields_variation ) ) {
                                        $fields = ( $fields_variation );
                                    }
                                }
                                break;
                        }
                    }
                }
            }

            // if fields is not empty
            if ( ! empty( $fields ) && is_array( $fields ) ) {
                foreach ( $fields as $key => $value ) {
                    if ( isset( Fnt_ProductListCons::$column_name_in_db[ $key ] ) ) {
                        $column_name_db_with_key = Fnt_ProductListCons::$column_name_in_db[ $key ];
                        $result[ $key ] = isset( $product[ $column_name_db_with_key ] ) ? $product[ $column_name_db_with_key ] : '';
                    }
                }
                // if have old product id
                if ( isset( $product[ 'old_product_id' ] ) ) {
                    $result[ 'old_product_id' ] = $product[ 'old_product_id' ];
                } else {
                    $result[ 'old_product_id' ] = 0;
                }
                // since ver 1.1, set parent product id for product type "Variation"
                // if have parent product id
                if ( isset( $product[ 'parent_product_id' ] ) ) {
                    $result[ 'parent_product_id' ] = $product[ 'parent_product_id' ];
                }
                //------------------------------

                // if product have product type
                if ( isset( Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ] ) ) {
                    $product_type_name_db = Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ];
                    $result[ Fnt_ProductListCons::COLUMN_TYPE ] = isset( $product[ $product_type_name_db ] ) ? $product[ $product_type_name_db ] : '';
                }
                $result[ Fnt_ProductListCons::COLUMN_MODIFYING_STATUS ] = isset( $product[ Fnt_ProductListCons::COLUMN_MODIFYING_STATUS ] ) ? $product[ Fnt_ProductListCons::COLUMN_MODIFYING_STATUS ] : -1;
            }

            return $result;
        }

        /**
         * Custom display the table
         *
         * @since 3.1.0
         * @access public
         */
        public function display() {
            $singular = $this->_args[ 'singular' ];
            $this->display_tablenav( 'top' );
            ?>
            <div class="wrapper-wp-list-table">
                <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
                    <thead>
                    <tr>
                        <?php $this->print_column_headers(); ?>
                    </tr>
                    </thead>

                    <tbody id="the-list"<?php
                        if ( $singular ) {
                            echo " data-wp-lists='list:$singular'";
                        } ?>>
                    <?php $this->display_rows_or_placeholder(); ?>
                    </tbody>

                    <tfoot>
                    <tr>
                        <?php $this->print_column_headers( false ); ?>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <?php
            $this->display_tablenav( 'bottom' );
            // Build popup editor
            $this->display_popup_editor();
            $this->display_popup_edit_product();
        }

        /**
         * This function will be render popup with the wp_editor inside
         * When click in buttons edit product description of buttons edit short description
         * We will remove class hidden of div wrapper to display popup
         * And read data of this product to fill in wp_editor, this code is javascript:
         * and code javascript of this process are write in ../assets/js/wp-editor-modal.js
         * Things to change when click button is:
         * - Title of popup: will be Edit description or Edit short description.
         * - The content of wp_editor(tinyMCE).
         * And we will be bind events for button cancel, save, and button close(x) in header.
         * Button cancel and button x are the same way, so that use same function, this don't change content of description
         * Some css are define in ../assets/css/stylesheet.css
         */
        private function display_popup_editor() {
            // some setting for wp_editor
            $settings = array(
                'editor_height'     => 400,
                'drag_drop_upload'  => true,
                'teeny'             => false
            );
            // set the id for editor
            $editor_id = 'product-popup-editor';
            ?>
            <!--Render the HTML for popup edit DESC/Short DESC-->
            <div class="bootstrap-wrapper hidden" id="popup-editor">
                <div class="bootbox modal fade in" tabindex="-1" role="dialog">
                    <div class="modal-backdrop fade in"></div>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="editor-title"><h4><?php echo __( 'Edit description', 'fnt' ); ?></h4></span>
                                <button type="button" class="popup-close-button close btn" data-dismiss="modal"
                                        aria-hidden="true">x
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="wp-editor-wrapper">
                                    <!--Call default editor of wordpress-->
                                    <?php wp_editor( '', $editor_id, $settings ); ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button data-bb-handler="cancel" type="button" class="btn button-cancel"><?php echo __( 'Cancel', 'fnt' ); ?></button>
                                <button data-bb-handler="save" type="button" class="btn button-save"><?php echo __( 'Save', 'fnt' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        private function display_popup_edit_product() {
            ?>
            <!--Render the HTML for popup iframe edit product-->
            <div class="bootstrap-wrapper hidden" id="popup-edit-product">
                <div class="bootbox modal fade in" tabindex="-1" role="dialog">
                    <div class="modal-backdrop fade in"></div>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="editor-title"><h4><?php echo __( 'Add product', 'fnt' ); ?></h4></span>
                                <button type="button" data-loading-text="x" class="popup-close-button close popup-button-control" data-dismiss="modal"
                                        aria-hidden="true">x
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="wp-editor-wrapper">
                                    <!--Icon loading-->
                                    <div class="wrap-center">
                                        <div class="center">
                                            <img src="<?php echo FNT_URL_PLUGIN.'/assets/images/spinner-small.gif'; ?>" />
                                            <span><h4><?php echo __( 'Loading...', 'fnt' ); ?></h4></span>
                                        </div>
                                    </div>
                                    <!--End icon loading-->
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-loading-text="Closing..." class="btn button-cancel popup-button-control"><?php echo __( 'Close', 'fnt' ); ?></button>
                                <button type="button" data-loading-text="Saving..." class="btn button-save popup-button-control"><?php echo __( 'Save', 'fnt' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        /*
         * This function will return the product in database for send to ajax to edit product on popup
         * @return array, this return product fields
         */
        public static function get_product_fields_by_product_id( $product_id ) {
            try {
                $result = array();
                $_pf = new WC_Product_Factory_Custom();
                // get product object by product_id
                $product = $_pf->get_product( $product_id );
                $product_data = array();
                $table_columns = self::static_get_columns();
                if ( ! empty( $table_columns ) && is_array( $table_columns) ) {
                    foreach ( $table_columns as $column_name => $column_value ) {
                        $product_data[ $column_name ] = self::render_table_cell_value( $product, $column_name );
                    }
                }
                $result['product_html_data'] = $product_data;
                // get product fields
                $product_data = $product->get_list_product_fields( $table_columns );
                // format array product fields
                $product_data = self::get_product_meta_to_array_static( $product_data );

                $result['product_js_data'] = $product_data;
                // since ver 1.1
                if ( $product->product_type == 'variable' ) {
                    $result['product_variation_data'] = self::ajax_render_variations_area_data();
                }
                // end since ver 1.1
                return array(
                    'result' => true,
                    'data' => $result
                );
            } catch ( Exception $ex ) {
                return array(
                    'result' => false,
                    'error-message' => $ex->getMessage()
                );
            }
        }

        /*
         ***********SINCE Quick edit product version 1.1**********
         * Add features for edit variable with variations products
         */
        /**
         * Since ver 1.1
         * Use to render table of variations via ajax call to sync when edit product with popup
         * And create data to replace in javascript
         * @return bool|array: false if lost any data, data content html and js
         */
        public static function ajax_render_variations_area_data() {
            $data = array();
            if ( isset( $_POST['product_id'] ) ) {
                $product_id = $_POST['product_id'];
            } else {
                return false;
            }
            $_pf = new WC_Product_Factory_Custom();
            // get product object by product_id
            $product = $_pf->get_product( $product_id );
            if ( isset( $_POST['screen_id'] ) ) {
                $screen_id = $_POST['screen_id'];
            } else {
                return false;
            }
            if ( ! $product ) {
                return false;
            }

            $screen = convert_to_screen( $screen_id );
            $args = array( 'screen' => $screen );

            $variation_table = new Fnt_VariationProductList( $args );
            // set parent product
            $variation_table->parent_product = $product;
            // init data for variation table
            $variation_table->init();
            ob_start();
            // show table variations
            $variation_table->display_rows_or_placeholder();
            $html_data = ob_get_clean();
            $data['variations_html_data'] = $html_data;

            // get selection default attributes
            $data['selection_default_attributes'] = $product->get_selection_variation_attributes();

            // get variations data
            $variations_js_data_temp = $variation_table->get_variation_product_data();
            $variations_js_data = array();
            if ( ! empty( $variations_js_data_temp ) ) {
                foreach ( $variations_js_data_temp as $variation_js_data_temp ) {
                    $variations_js_data[] = self::get_product_meta_to_array_static( $variation_js_data_temp );
                }
            }

            $data['variations_js_data'] = self::get_final_product_data_to_js( $variations_js_data );

            return $data;
        }

        /**
         * Since ver 1.1
         * Use to render table of attributes via ajax call to sync when edit product with popup
         * And create data to replace in javascript
         * @return bool|array: false if lost any data, data content html and js
         */
        public static function ajax_render_attributes_area_data() {
            $data = array();
            if ( isset( $_POST['product_id'] ) ) {
                $product_id = $_POST['product_id'];
            } else {
                return false;
            }
            $_pf = new WC_Product_Factory_Custom();
            // get product object by product_id
            $product = $_pf->get_product( $product_id );
            if ( isset( $_POST['screen_id'] ) ) {
                $screen_id = $_POST['screen_id'];
            } else {
                return false;
            }
            if ( ! $product ) {
                return false;
            }

            $screen = convert_to_screen( $screen_id );
            $args = array( 'screen' => $screen );

            $attributes_table = new Fnt_ProductAttributes( $args );
            // set current product
            $attributes_table->product = $product;
            // init data for attributes table
            $attributes_table->init();

            $attributes_data = $attributes_table->get_attributes_product_data();
            ob_start();
            // show table attributes
            $attributes_table->display_rows_or_placeholder();
            $html_data = ob_get_clean();

            $data['selection_option_attribute'] = $attributes_table->render_selection_attributes();

            $data['attributes_html_data'] = $html_data;
            // get attributes js data

            $data['attributes_js_data'] = array(
                $product_id => $attributes_data
            );

            return $data;
        }
        /**
         * @since 1.1
         * @param $item
         * @param $alternate
         */
        private function variations_single_row( $item, &$alternate ) {
            // so we will get product variations of exists product
            $product_id = $item->id;
//            $real_product_id = $item->get_product_id(); // get real product id
//            // check if product is have old product
//            if ( $product_id != $real_product_id ) {
//                $_pf = new WC_Product_Factory_Custom();
//                // get exists product
//                $product = $_pf->get_product( $real_product_id );
//                // if get success, change current $item to real product
//                if ( $product ) {
//                    $item = $product;
//                }
//            }

            // render variations area
            $alternate = 'alternate' == $alternate ? '' : 'alternate';
            echo "<tr class='$alternate'>";
            echo '</tr>';
            $alternate = 'alternate' == $alternate ? '' : 'alternate';

            echo "<tr class='hidden variation-wrapper-row variations-row-of-$product_id $alternate' parent-id='$product_id'>";
            $this->variations_single_row_column( $item );
            echo '</tr>';

            // render attributes area
            $alternate = 'alternate' == $alternate ? '' : 'alternate';
            echo "<tr class='$alternate'>";
            echo '</tr>';
            $alternate = 'alternate' == $alternate ? '' : 'alternate';
            echo "<tr class='hidden attributes-wrapper-row attributes-row-of-$product_id $alternate' product-id='$product_id'>";
            $this->attributes_single_row_column( $item );
            echo '</tr>';
        }

        /**
         * @since 1.1
         * @param $item
         */
        private function attributes_single_row_column( $item ) {
            echo '<td colspan="'.$this->columns_count.'">';
            echo '<div id="attributes-content-wrapper-of-'.$item->id.'" class="attributes-content-wrapper">';
            ?>
            <div class="bootstrap-wrapper popup-attributes-wrapper" id="popup-attributes">
                <div class="bootbox modal fade in" tabindex="-1" role="dialog">
                    <div class="modal-backdrop fade in"></div>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="popup-title"><h4><?php echo __( 'Edit attributes of #' . $item->get_product_id() . ', name: ' . $item->get_post_data()->post_title, 'fnt' ); ?></h4></span>
                            </div>
                            <div class="modal-body">
                                <?php $this->render_attributes_area( $item ); ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" product-id="<?php echo $item->id; ?>" class="btn button-hide-attributes"><?php echo __( 'Close', 'fnt' ); ?></button>
                                <button type="button" product-id="<?php echo $item->id; ?>" class="btn button-primary button-save-attributes"><?php echo __( 'Save attributes', 'fnt' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo '</div>';
            echo '</td>';
        }

        /**
         * @since 1.1
         * @param $item
         */
        private function render_attributes_area( $item ) {
            $attributes_table = new Fnt_ProductAttributes();
            // set current product
            $attributes_table->product = $item;
            // init data for attributes table
            $attributes_table->init();
            // show table attributes
            $attributes_table->display();

            $attributes_data = $attributes_table->get_attributes_product_data();
            $this->current_attributes[$item->id] = $attributes_data;
        }

        /**
         * @since 1.1
         * @param $item
         */
        private function variations_single_row_column( $item ) {
            echo '<td colspan="'.$this->columns_count.'">';
            echo '<div id="variations-content-wrapper-of-'.$item->id.'" class="variations-content-wrapper">';
            ?>
            <div class="bootstrap-wrapper popup-variations-wrapper" id="popup-variations">
                <div class="bootbox modal fade in" tabindex="-1" role="dialog">
                    <div class="modal-backdrop fade in"></div>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="popup-title"><h4><?php echo __( 'Edit variations of #' . $item->get_product_id() . ', name: ' . $item->get_post_data()->post_title, 'fnt' ); ?></h4></span>
                            </div>
                            <div class="modal-body">
                                <?php $this->render_variations_area( $item ); ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" parent-id="<?php echo $item->id; ?>" class="btn variation-button-cta button-hide-variation"><?php echo __( 'Close', 'fnt' ); ?></button>
                                <button type="button" parent-id="<?php echo $item->id; ?>" class="btn button-primary variation-button-cta button-save-variation"><?php echo __( 'Save variations', 'fnt' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo '</div>';
            echo '</td>';
        }
        /**
         * @since 1.1
         * @param $item
         */
        private function render_variations_area( $item ) {
            $variation_table = new Fnt_VariationProductList();
            // set parent product
            $variation_table->parent_product = $item;
            // init data for variation table
            $variation_table->init();
            // show table variations
            $variation_table->display();
            // get data of variation to localize to javascript
            $variations_data = $variation_table->get_variation_product_data();

            // browse through all products variation if not empty
            // add products variation to array product
            if ( ! empty ( $variations_data ) ) {
                foreach ( $variations_data as $variation_data ) {
                    $this->products[] = $variation_data;
                }
            }
        }

        public function get_full_product_attributes() {
            return $this->current_attributes;
        }
    }

    /*
     * Set default tab in wp_editor is visual tab
     * this use with wp_editor popup
     */
    add_filter( 'wp_default_editor', 'wpse101200_default_editor', 999 );
    function wpse101200_default_editor( $editor ) {
        return 'tinymce';
    }
    // hidden button view full screen of wp_editor
    add_filter( 'mce_buttons', 'fnt_remove_fullscreen_mce_buttons', 999 );


    /**
     * Since ver 1.1
     * Render variation product with variable
     * Class Fnt_VariationProductList
     */
    class Fnt_VariationProductList extends WP_List_Table {
        public $parent_product;
        public $parent_id;
        private $message_when_item_empty = 'No variation products available.';

        public function __construct( $args = array() ) {
            parent::__construct( array(
                                     'singular' => __( 'Variation', 'sp' ),
                                     'plural'   => __( 'Variations', 'sp' ),
                                     'ajax'     => false,
                                     'screen'   => isset( $args['screen'] ) ? $args['screen'] : null
                                 ) );
        }

        // This function use to show the message when don't have any product in list
        public function no_items() {
            _e( $this->message_when_item_empty, 'fnt' );
        }

        public function init() {
            $this->prepare_items();
            // get product variations of current product
            $variation_attributes = $this->parent_product->get_attributes();
            $variations = $this->parent_product->get_available_variations();
            if ( ! empty( $variations ) && empty( $variation_attributes ) ) {
                $this->message_when_item_empty = 'Please add attributes to enable edit variations.';
                $variations = array();
            } else {
                $this->message_when_item_empty = 'No variation products available.';
            }
            // check get success or not
            $variations = is_array( $variations ) ? $variations : array();
            $this->items = $variations;
            // set parent ID of variations
            $this->parent_id = $this->parent_product->id;
        }

        /**
         * @return array, columns show in table header
         * Add 'var' string to async with current screen options
         */
        public function get_columns() {
            $columns = array (
                'product_id' => '<input class="cb-select-all-variations header-checkbox" type="checkbox">',
                Fnt_ProductListCons::COLUMN_ID . '-variation'              => __( 'ID', 'fnt' ),
                Fnt_ProductListCons::COLUMN_ATTRIBUTE . '-variation'       => __( 'Attributes', 'fnt' ),
                Fnt_ProductListCons::COLUMN_THUMBNAIL . '-variation'       => '<span class="wc-image" title="' . __( 'Thumbnail', 'fnt' ) . '">' . __( 'Thumbnail', 'fnt' ) . '</span>',
                Fnt_ProductListCons::COLUMN_SKU . '-variation'             => __( 'SKU', 'fnt' ),
                Fnt_ProductListCons::COLUMN_STOCK . '-variation'           => __( 'Stock', 'fnt' ),
                Fnt_ProductListCons::COLUMN_MANAGE_STOCK . '-variation'    => __( 'M.Stock', 'fnt' ),
                Fnt_ProductListCons::COLUMN_STOCK_QUANTITY . '-variation'  => __( 'Quantity', 'fnt' ),
                Fnt_ProductListCons::COLUMN_BACK_ORDERS . '-variation'     => __( 'Back Orders', 'fnt' ),
                Fnt_ProductListCons::COLUMN_REGULAR_PRICE . '-variation'   => __( 'R.Price', 'fnt' ),
                Fnt_ProductListCons::COLUMN_SALE_PRICE . '-variation'      => __( 'S.Price', 'fnt' ),
                Fnt_ProductListCons::COLUMN_WEIGHT . '-variation'          => __( 'Weight', 'fnt' ) . ' (' . Fnt_Core::get_weight_unit() . ')',
                Fnt_ProductListCons::COLUMN_LENGTH . '-variation'          => __( 'Length', 'fnt' ) . ' (' . Fnt_Core::get_dimension_unit() . ')',
                Fnt_ProductListCons::COLUMN_WIDTH . '-variation'           => __( 'Width', 'fnt' ) . ' (' . Fnt_Core::get_dimension_unit() . ')',
                Fnt_ProductListCons::COLUMN_HEIGHT . '-variation'          => __( 'Height', 'fnt' ) . ' (' . Fnt_Core::get_dimension_unit() . ')'
            );
            return $columns;
        }

        /**
         * Prepare data for display table: columns header and product list data
         */
        public function prepare_items() {
            // set columns header
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = array();
            $this->_column_headers = array($columns, $hidden, $sortable);
        }

        public function single_row( $item ) {
            $attrs = '';
            $class_color = '';
            // check if this product is just add
            if ( $item->is_just_add() ) {
                // get color in option database
                $just_add_color = Fnt_Core::get_just_add_color();
                // if this option is not define in database, add class default
                if ( empty( $just_add_color ) ) {
                    $class_color = 'adding-row-color';
                } else { // if have option, set color in style for row
                     $attrs.= " style='background-color: {$just_add_color}!important;'";
                }
            }
            echo "<tr class='variation-row $class_color' $attrs>";
            $this->single_row_columns( $item );
            echo '</tr>';
        }

        /**
         * Custom way display the table
         * @since 3.1.0
         * @access public
         */
        public function display() {
            $singular = $this->_args[ 'singular' ];
            $this->display_tablenav( 'top' );
            ?>
            <div class="wrapper-wp-list-variation-table">
                <table class="wp-list-variation-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
                    <thead>
                    <tr>
                        <?php $this->print_column_headers(); ?>
                    </tr>
                    </thead>

                    <tbody id="the-list-variations-of-<?php echo $this->parent_id; ?>" class="the-list-variations"<?php
                        if ( $singular ) {
                            echo " data-wp-lists='list:$singular'";
                        } ?>>
                    <?php $this->display_rows_or_placeholder(); ?>
                    </tbody>
                </table>
            </div>
            <?php
            $this->display_tablenav( 'bottom' );
        }

        /**
         * Mapping column to get correct column to get data
         * @return array
         */
        private static function prepare_variation_columns() {
            $variation_columns = Fnt_ProductListCons::$variation_columns;
            $variation_columns_prepare = array();
            foreach ( $variation_columns as $key => $value ) {
                $variation_columns_prepare[$key . '-variation'] = $value;
            }
            return $variation_columns_prepare;
        }

        public function column_product_id( $item ) {
            $product_id = isset( $item->id ) ? $item->id : '';
            $extra_attr = '';
            if ( $product_id != $item->get_product_id() ) {
                $extra_attr = " old-product-id = '{$item->get_product_id()}' ";
            }
            $cb = "<input type='checkbox' class='product-single-checkbox-row' $extra_attr data-product-id='$product_id' data-product-row-id='row-$product_id' name='bulk-delete[]' value='$product_id' />";
            $hidden_fields = "<input type='hidden' name='products[$product_id][is-modifying]' class='input-is-modifying' value='no' />";

            return $cb . $hidden_fields;
        }

        /**
         * @param object $item, variation product
         * @param string $column_name, current column
         * @return string, html to show to table cell
         */
        public function column_default( $item, $column_name ) {
            $variation_columns = self::prepare_variation_columns();
            $column_name = $variation_columns[ $column_name ];
            $result = Fnt_CustomProductList::render_table_cell_value( $item, $column_name );
            return $result;
        }

        /**
         * Show content in top and bottom of table
         * @param string $which: 'top' or 'bottom'
         */
        public function extra_tablenav( $which ) {
            if ( $which == 'top' ) {
                ?>
                <div class="alignleft actions">
                    <button type="button" disabled="disabled" class="button button-primary fnt-button-cta button-add-variation" >
                        <?php echo __( 'Add new', 'fnt' ); ?>
                    </button>
                    <button type="button" disabled="disabled" class="button fnt-button-cta button-delete-variations" >
                        <?php echo __( 'Delete', 'fnt' ); ?>
                    </button>
                </div>

                <div class="alignleft" id = "selection-default-attribute-wrapper-<?php echo $this->parent_id;?>">
                    <?php echo $this->parent_product->get_selection_variation_attributes(); ?>
                </div>
                <?php
            }
            if ( $which == 'bottom' ) {
//                $this->get_control_buttons();
                echo '';
            }
        }

        /**
         * @param string $which
         */
        public function display_tablenav( $which ) {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                ?>
                <br class="clear"/>
            </div>
            <?php
        }

        /**
         * Made button 'Save' and 'Cancel'
         */
        private function get_control_buttons() {
            $attrs = 'parent-id="'.$this->parent_id.'"';
            ?>
            <div class="alignleft actions">
<!--                <button type="button" class="button button-primary variation-button-cta button-save-variation"<?php //echo $attrs;?>>-->
<!--                    --><?php //echo __( 'Save', 'fnt' ); ?>
<!--                </button>-->
<!--                <button type="button" class="button variation-button-cta button-cancel-variation"<?php //echo $attrs;?>>-->
<!--                    --><?php //echo __( 'Cancel', 'fnt' ); ?>
<!--                </button>-->
                <button type="button" class="button button-primary variation-button-cta button-hide-variation"<?php echo $attrs;?>>
                    <?php echo __( 'Hide variations', 'fnt' ); ?>
                </button>
            </div>
            <?php
        }

        /**
         * Get all product data to localize to javascript
         * @return array
         */
        public function get_variation_product_data() {
            $variations_data = array();
            if ( ! empty ( $this->items ) ) {
                foreach ( $this->items as $variation ) {
                    $variations_data[] = $variation->get_list_product_fields( Fnt_ProductListCons::$variation_columns );
                }
            }

            return $variations_data;
        }
    }

    /**
     * Since ver 1.1
     * Use to render product attributes of product variable and simple
     * Class Fnt_ProductAttributes
     */
    class Fnt_ProductAttributes extends WP_List_Table {
        public $product; // current product
        public $product_id; // current product id
        private $attributes; // attributes of current product

        public function __construct( $args = array() ) {
            parent::__construct( array(
                                     'singular' => __( 'Attribute', 'sp' ),
                                     'plural'   => __( 'Attributes', 'sp' ),
                                     'ajax'     => false,
                                     'screen'   => isset( $args['screen'] ) ? $args['screen'] : null
                                 ) );
        }

        // This function use to show the message when don't have any product in list
        public function no_items() {
            _e( 'No attributes available.', 'fnt' );
        }

        public function init() {
            $this->prepare_items();
            // get product attributes of current product
            $attributes = $this->product->get_attributes();
            // check get success or not
            $attributes = is_array( $attributes ) ? $attributes : array();
            $this->attributes = $attributes; // get attributes before insert item 'key' for each attribute by function get_attributes()
            $this->items = $this->get_attributes( $attributes );
            // set product ID
            $this->product_id = $this->product->id;
        }

        /**
         * Insert key to each item attribute
         * @param $attributes
         * @return array
         */
        private function get_attributes( $attributes ) {
            if ( empty( $attributes ) ) {
                return array();
            }
            // add item key for attribute
            foreach ( $attributes as $key => $attribute ) {
                $attributes[$key]['key'] = $key;
            }

            return $attributes;
        }

        /**
         * @return array, columns show in table header
         * Add 'var' string to async with current screen options
         */
        public function get_columns() {
            $columns = array (
                'position'   => __( 'Sort', 'fnt'),
                'action'     => __( 'Action(s)', 'fnt'),
                'name'       => __( 'Name', 'fnt'),
                'value'      => __( 'Value(s)', 'fnt'),
                'is_visible' => __( 'Visible on the product page', 'fnt')
            );
            if ( ( isset( $this->product->product_type ) && $this->product->product_type == 'variable' )
            || ( isset( $_POST['product_type'] ) && $_POST['product_type'] == 'variable' ) ) {
                $columns['is_variation'] = __( 'Used for variations', 'fnt');
            }
            return $columns;
        }

        /**
         * Prepare data for display table: columns header and product list data
         */
        public function prepare_items() {
            // set columns header
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = array();
            $this->_column_headers = array( $columns, $hidden, $sortable );
        }

        /**
         * Custom way display the table
         * @since 3.1.0
         * @access public
         */
        public function display() {
            $singular = $this->_args[ 'singular' ];
            $this->display_tablenav( 'top' );
            ?>
            <div class="wrapper-wp-list-attributes-table">
                <table class="wp-list-attributes-table <?php echo implode( ' ', $this->get_table_classes() ); ?>" product-id="<?php echo $this->product_id; ?>">
                    <thead>
                    <tr>
                        <?php $this->print_column_headers(); ?>
                    </tr>
                    </thead>

                    <tbody id="the-list-attributes-of-<?php echo $this->product_id; ?>" class="the-list-attributes"<?php
                        if ( $singular ) {
                            echo " data-wp-lists='list:$singular'";
                        } ?>>
                    <?php $this->display_rows_or_placeholder(); ?>
                    </tbody>
                </table>
            </div>
            <?php
            $this->display_tablenav( 'bottom' );
        }

        public function single_row( $attribute ) {
            static $alternate;
            $attribute_key = $attribute['key'];
            $attrs = 'attribute-key="'.$attribute_key.'"';
            $attrs .= ' product-id="'.$this->product_id.'"';
            $attrs .= ' is-taxonomy="'.$attribute['is_taxonomy'].'"';
            $alternate = 'alternate' == $alternate ? '' : 'alternate';
            $taxonomy = $attribute['key'];
            echo "<tr class='attribute-row $alternate $taxonomy$this->product_id' $attrs>";
            $this->single_row_columns( $attribute );
            echo '</tr>';
        }

        /**
         * @param object $attribute, attribute of product
         * @param string $column_name, current column
         * @return string, html to show to table cell
         */
        public function column_default( $attribute, $column_name ) {
            return 'You must custom in func column default';
        }

        public function column_position( $attribute ) {
            return '<span class="attribute-position bootstrap-wrapper" attribute-item-key="position" value="'.$attribute['position'].'">
                        <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
                    </span>';
        }

        /**
         * Render checkbox for is_variation option
         * @param $attribute
         * @return string
         */
        public function column_is_variation( $attribute ) {
            $attrs = "attribute-item-key='is_variation'";
            if ( isset( $attribute['is_variation'] ) ) {
                $attrs = $attribute['is_variation'] == 1 ? $attrs. ' checked="checked"' : $attrs;
            }
            return '<input type="checkbox" class="attribute-is-variation attribute-checkbox" '.$attrs.' />';
        }

        /**
         * Render checkbox for is_visible option
         * @param $attribute
         * @return string
         */
        public function column_is_visible( $attribute ) {
            $attrs = "attribute-item-key='is_visible'";
            if ( isset( $attribute['is_visible'] ) ) {
                $attrs = $attribute['is_visible'] == 1 ? $attrs. ' checked="checked"' : $attrs;
            }
            return '<input type="checkbox" class="attribute-is-visible attribute-checkbox" '.$attrs.' />';
        }

        public static function render_default_attributes_value( $product_id, $attribute ) {
            $attribute_html = '';
            $taxonomy = $attribute['name'];
            $attribute_taxonomy = Fnt_Core::get_attribute_taxonomy( $taxonomy );
            if ( is_object( $attribute_taxonomy ) ) {
                $attrs = "attribute-item-key='value'";
                // note: location html-product-attribute.php/WC_AJAX
                if ( $attribute_taxonomy->attribute_type == 'select' ) {
                    $select_placeholder = __( 'Select terms', 'fnt' );
                    $attribute_div = '<div class="attribute-items">';

                    $attribute_select_hidden = '<select class="attribute-value hidden" multiple="multiple" data-placeholder="'.$select_placeholder.'" '.$attrs.'>';

                    $attribute_select_single = '<select class="attribute-select-item" data-placeholder="'.$select_placeholder.'" '.$attrs.'>';
                    $all_terms = get_terms( $taxonomy, 'orderby=name&hide_empty=0' );
                    if ( $all_terms ) {
                        foreach ( $all_terms as $term ) {
                            $has_term = has_term( absint( $term->term_id ), $taxonomy, $product_id );
                            if ( $has_term ) {
                                $attribute_div .= '<span class="wrapper-attribute-item bootstrap-wrapper">
                                                        <span class="glyphicon glyphicon-remove remove-attribute-item" item-slug="'.esc_attr( $term->slug ).'"></span>
                                                        '.$term->name.'
                                                    </span>';
                            }
                            $hidden = $has_term ? 'hidden' : '';
                            $attribute_select_hidden .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $has_term, true, false ) . '>' . $term->name . '</option>';
                            $attribute_select_single .= '<option class="'.$hidden.'" value="' . esc_attr( $term->slug ) .'">' . $term->name . '</option>';
                        }
                    }
                    $attribute_select_hidden .= '</select>';
                    $attribute_select_single .= '</select>';
                    $attribute_div .= '</div>';
                    $button_attrs = 'taxonomy="'.$taxonomy.'"';
                    $button_add_attr_term = '<button type="button" class="button button-add-attribute-term" ' . $button_attrs . ' >'. __( 'Add new', 'fnt' ) .'</button>';
                    $attribute_html = $attribute_select_hidden . $attribute_select_single . $button_add_attr_term . $attribute_div;
                } elseif( $attribute_taxonomy->attribute_type == 'text' ) {
                    $input_value = esc_attr( implode( ' ' . WC_DELIMITER . ' ', wp_get_post_terms( $product_id, $taxonomy, array( 'fields' => 'names' ) ) ) );
                    $placeholder = esc_attr( sprintf( __( '"%s" separate terms', 'woocommerce' ), WC_DELIMITER ) );
                    $attribute_html = '<input class="attribute-value attribute-value-input" type="text" value="'.$input_value.'" placeholder="'.$placeholder.'" '.$attrs.' />';
                } else {
                    $attribute_html = 'Not yet supported.';
                }
            }
            return $attribute_html;
        }

        /**
         * Render value of attribute
         * @param $attribute
         * @return string
         */
        public function column_value( $attribute ) {
            $attrs = "attribute-item-key='value'";
            if ( $attribute['is_taxonomy'] == 1 ) {
                return self::render_default_attributes_value( $this->product_id, $attribute );
            }

            $placeholder = __( 'Enter some text, or some attributes by &quot;'.WC_DELIMITER.'&quot; separating values.', 'fnt' );
            return '<textarea placeholder="'.$placeholder.'" rows="3" class="attribute-value attribute-area" '.$attrs.' >'.$attribute['value'].'</textarea>';
        }

        /**
         * Render column name of attribute
         * At the present, just allow edit name of custom attributes
         * @param $attribute
         * @return string
         */
        public function column_name( $attribute ) {
            $attrs = "attribute-item-key='name'";
            if ( $attribute['is_taxonomy'] == 1 ) {
                $taxonomy_name = '<input type="hidden" class="attribute-name" value="'.$attribute['name'].'" '.$attrs.' />';
                $taxonomy_name .= '<span class="attribute-name-taxonomy">' . wc_attribute_label( $attribute['name'] ) . '</span>';
                return $taxonomy_name;
            }

            return '<input type="text" placeholder="Attribute name" class="attribute-name" value="'.$attribute['name'].'" '.$attrs.' />';
        }

        /**
         * Render action for cell actions
         * @param attribute
         * @return string
         */
        public function column_action( $attribute ) {
            if ( empty( $attribute ) ) {
                return '';
            }
            $attrs = "attribute-key='{$attribute['key']}'";
            $attrs .= "product-id='{$this->product_id}'";
            // add link action for each attribute
            $actions = array(
                'remove' => sprintf( "<a class='remove-attribute attribute-actions' %s>%s</a>", $attrs, __('Remove', 'fnt' ) )
            );
            $actions = apply_filters( 'fnt_product_attributes_actions', $actions );
            return $this->row_actions( $actions, true );
        }

        /**
         * Show content in top and bottom of table
         * @param string $which: 'top' or 'bottom'
         */
        public function extra_tablenav( $which ) {
            if ( $which == 'top' ) {
                $attrs = 'product-id="'.$this->product_id.'"';
                ?>
                <div class="alignleft" id = "selection-option-add-attribute-wrapper-<?php echo $this->product_id;?>">
                    <?php echo $this->render_selection_attributes(); ?>
                </div>
                <div class="alignleft fnt-button-add-attribute">
                    <button type="button" class="button button-primary attribute-button-cta button-add-attribute" <?php echo $attrs;?>>
                        <?php echo __( 'Add Attribute', 'fnt' ); ?>
                    </button>
                </div>
                <?php
            }
            if ( $which == 'bottom' ) {
//                $this->get_control_buttons();
                echo '';
            }
        }

        /**
         * @param string $which
         */
        public function display_tablenav( $which ) {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>">
                <?php
                    $this->extra_tablenav( $which );
                    $this->pagination( $which );
                ?>
                <br class="clear"/>
            </div>
            <?php
        }

        /**
         * Made button 'Save' and 'Cancel'
         */
        private function get_control_buttons() {
            $attrs = 'product-id="'.$this->product_id.'"';
            ?>
            <div class="alignleft actions">
                <button type="button" class="button button-primary attribute-button-cta button-save-attributes" <?php echo $attrs;?>>
                    <?php echo __( 'Save Attributes', 'fnt' ); ?>
                </button>
                <button type="button" class="button attribute-button-cta button-hide-attributes"<?php echo $attrs;?>>
                    <?php echo __( 'Hide Attributes', 'fnt' ); ?>
                </button>
            </div>
            <?php
        }

        /**
         * Get all product attributes data to localize to javascript
         * @return array
         */
        public function get_attributes_product_data() {
            return $this->attributes;
        }

        // defined default value for new attribute
        public static function get_default_attribute() {
            return array(
                'name'         => '',
                'value'        => '',
                'position'     => -1,
                'is_visible'   => 1,
                'is_variation' => 0,
                'is_taxonomy'  => 0
            );
        }

        /**
         * Render a blank attribute row
         * @return bool|array: false if have error, array content html data and js data
         */
        public function render_blank_attribute_row() {
            if ( isset( $_POST['is_taxonomy'] ) && isset( $_POST['selected_attribute_value'] ) && isset( $_POST['productID'] ) ) {
                $is_taxonomy = absint( $_POST['is_taxonomy'] );
                $taxonomy_name = $_POST['selected_attribute_value'];
                $productID = absInt( $_POST['productID'] );
            } else {
                return false;
            }

            $default_attribute = self::get_default_attribute();

            $default_attribute['is_taxonomy'] = $is_taxonomy;
            if ( $is_taxonomy == 1 ) {
                $default_attribute['name'] = $taxonomy_name;
            }

            // render js var
            $js_data = $default_attribute;

            // render html
            $default_attribute['key'] = $is_taxonomy == 1 ? $taxonomy_name : ''; // extra value
            $attrs = ' is-taxonomy="'.$default_attribute['is_taxonomy'].'"';
            $attribute_key = $default_attribute['key'];
            $attrs .= 'attribute-key="'.$attribute_key.'"';
            $this->prepare_items(); // set columns for table row
            $taxonomy = $default_attribute['name'];
            $attrs .= ' product-id="'.$productID.'"';
            ob_start();
            echo "<tr class='attribute-row blank_attribute $taxonomy$productID' $attrs>";
            $this->single_row_columns( $default_attribute );
            echo '</tr>';
            $html_data = ob_get_clean();

            $attributes_data = array();
            $attributes_data['js']   = $js_data;
            $attributes_data['html'] = $html_data;

            return $attributes_data;
        }

        /**
         * Get list default name attributes of current product
         * @return array
         */
        private function get_default_attributes_name() {
            $product_attributes = $this->product->get_attributes();
            $default_attributes_name = array();
            foreach ( $product_attributes as $product_attribute ) {
                if ( $product_attribute['is_taxonomy'] == 1 ) {
                    $default_attributes_name[] = $product_attribute['name'];
                }
            }

            return $default_attributes_name;
        }

        /**
         * Get option to select custom or default attribute to add new attribute
         * @return string
         */
        public function render_selection_attributes() {
            $default_attributes_name = $this->get_default_attributes_name();

            $select_html = '<select name="attribute_taxonomy" class="add_attribute_taxonomy">';
            $select_html .= '<option class="add_custom_attribute" value="fnt_add_custom_attribute">' . __( 'Custom product attribute', 'woocommerce' ) . '</option>';
            // Array of defined attribute taxonomies
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            if ( $attribute_taxonomies ) {
                foreach ( $attribute_taxonomies as $tax ) {
                    $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                    $label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                    $disabled = in_array( $attribute_taxonomy_name, $default_attributes_name ) ? 'disabled="disabled"' : '';
                    $select_html .= '<option '.$disabled.' value="' . esc_attr( $attribute_taxonomy_name ) . '">' . esc_html( $label ) . '</option>';
                }
            }
            $select_html .= '</select>';
            return $select_html;
        }
    }
