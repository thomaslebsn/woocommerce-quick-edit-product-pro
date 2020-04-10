<?php

    class Fnt_Url_Handler {
        private static $sql_args = array(
            'from'      => '',
            'where'     => '',
            'order_by'  => '',
            'order'     => ''
        );

        private static $url_args = array(
            'post_type' => 'product' // default post type is product
        );

        /**
         * get list product id to insert this to query string
         * @param $products
         * @return string
         */
        private static function get_list_product_id( $products ) {
            $ids = '';
            if ( ! empty( $products ) && is_array( $products ) ) {
                foreach ( $products as $product ) {
                    if ( isset( $product[ 'ID' ] ) && ! empty( $product[ 'ID' ] ) ) {
                        $ids .= $product[ 'ID' ] . ', ';
                    }
                }
            }

            return trim( $ids, ', ' );
        }

        /**
         * Get current post type in url
         * @return string, the post type of current page
         */
        public static function get_product_type() {
            global $wpdb;
            // get args in url
            self::$url_args = self::get_args_from_url();
            // if current page show just import product, post_type will is 'temp'. Only with Woocommerce 2.6.x
            // post_type is force to "product" from woocommerce version 3.x
            if ( self::is_just_import_product() ) {
                // investigating
                self::$url_args[ 'post_type' ] = 'product';
            }
            // return a post type
            $have_post_type = isset( self::$url_args[ 'post_type' ] ) && ! empty( self::$url_args[ 'post_type' ] ) ;
            $product_type = $have_post_type ?  self::$url_args[ 'post_type' ] : 'product';
            $wpdb->escape_by_ref( $product_type );
            return $product_type;
        }

        /**
         * get current cat id by analyst args in url, that is the selected category in drop down list categories
         * @param $column_name, the column name
         * @return int, the ID of term is selected | return 0 if not found
         */
        public static function get_current_term_id( $column_name ) {
            self::$url_args = self::get_args_from_url();
            if ( self::is_just_import_product() ) {
                self::$url_args[ 'post_type' ] = 'product'; // investigating
            }
            $term_id = 0;
            // if value defined
            $column_defined = isset( Fnt_ProductListCons::$column_name_in_db[ $column_name ] ) && ! empty( Fnt_ProductListCons::$column_name_in_db[ $column_name ] );
            if ( $column_defined ) {
                $column_term = Fnt_ProductListCons::$column_name_in_db[ $column_name ];
                if ( isset( self::$url_args[ $column_term ] ) ) {
                    $term_id = self::$url_args[ $column_term ];
                }
            }

            $term_validation = isset( $term_id ) && ! empty( $term_id ) && is_numeric( $term_id );
            return $term_validation ? $term_id : 0;
        }

        public static function get_current_product_type_change_to() {
            return isset( $_GET['change_product_type'] ) ? $_GET['change_product_type'] : 0;
        }

        /**
         * @param string $current_url, if have url input, we will get params in this url
         * If don't have url input, then we get the current url in browser
         * @return array params|null if don't have params
         */
        private static function get_args_from_url( $current_url = '' ) {
            global $export_url;
            if ( isset( $export_url ) && ! empty( $export_url ) ) {
                $current_url = $export_url;
            }
            if ( empty( $current_url ) ) {
                if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
                    $current_url = $_SERVER[ 'REQUEST_URI' ];
                }
            }
            // get params by url
            return Fnt_QEPP::custom_parse_str( $current_url );
        }

        /**
         * Function use to search product by NAME or SKU
         * @param string $value_search
         * This is put the where query to $sql_args
         */
        private static function search_product( $value_search = '' ) {
            // if have value_search then we will search
            if ( ! empty( $value_search ) ) {
                global $wpdb;
                // Begin search by SKU
                $value_search = $wpdb->esc_like( $value_search );
                $value_search = '%' . $value_search . '%';
                // prepare query to search by sku
                $sql = "SELECT post_id AS ID FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE %s ";
                $sql = $wpdb->prepare( $sql, $value_search );
                $product_id_by_sku = $wpdb->get_results( $sql, 'ARRAY_A' );
                // get list ID is have SKU
                $in = self::get_list_product_id( $product_id_by_sku );
                // End search by SKU
                // if don't have product in search by sku
                if ( empty( $in ) ) {
                    self::$sql_args[ 'where' ] .= $wpdb->prepare("\n AND {$wpdb->posts}.post_title LIKE %s ",$value_search);
                } else { // if have product in search by sku
                    self::$sql_args[ 'where' ] .= $wpdb->prepare("\n AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.ID IN ($in))", $value_search);
                }
            }
        }

        /**
         * Function use to filter product by month
         * @param string $month, data in current url
         */
        private static function get_filter_by_month( $month = '' ) {
            global $wpdb;
            if ( ! empty( $month ) && $month != '0' ) {
                $month_convert = substr( $month, 0, 4 ) . '-' . substr( $month, 4, 2 );
                $start_date = $month_convert . '-01';
                $end_date = date( 'Y-m-t', strtotime( $start_date ) );
                self::$sql_args[ 'where' ] .= $wpdb->prepare( "\n AND (CONVERT( {$wpdb->posts}.post_date, DATE) BETWEEN %s AND %s)", $start_date, $end_date );
            }
        }

        /**
         * Function use to filter product by category
         * @param $category_id, in current url
         */
        private static function get_filter_by_category( $category_id ) {
            global $wpdb;
            if ( ! empty( $category_id ) && $category_id != '0' ) {
                if ( $category_id == OPTION_NONE_VALUE ) {
                    $products = Fnt_Core::get_products_uncategorized();
                } else {
                    $products = Fnt_Core::get_products_by_term_id( $category_id );
                }
                $in = self::get_list_product_id( $products );
                if ( empty( $in ) ) {
                    $in = '-99999';
                }
                self::$sql_args[ 'where' ] .= "\n AND {$wpdb->posts}.ID IN ($in)";
            }
        }

        /**
         * Filter product by product type id
         * @param $type_id
         */
        private static function get_filter_by_type( $type_id ) {
            global $wpdb;
            if ( ! empty( $type_id ) && $type_id != '0' ) {
                $products = Fnt_Core::get_products_by_term_id( $type_id );
                $in = self::get_list_product_id( $products );
                if ( empty( $in ) ) {
                    $in = '-99999';
                }
                self::$sql_args[ 'where' ] .= "\n AND {$wpdb->posts}.ID IN ($in)";
            }
        }

        /**
         * Filter product by product status
         * @param $status
         */
        private static function get_filter_by_status( $status ) {
            global $wpdb;
            if ( ! empty( $status ) ) {
                $wpdb->escape_by_ref($status);
                self::$sql_args[ 'where' ] .= $wpdb->prepare("\n AND {$wpdb->posts}.post_status = %s", $status);
            }
        }

        /**
         * Use to order product by data in table postmeta
         * @param $order_by
         */
        private static function get_order_by_wp_postmeta_field( $order_by ) {
            global $wpdb;
            if ( isset( Fnt_ProductListCons::$column_format[ $order_by ] ) && ! empty( Fnt_ProductListCons::$column_format[ $order_by ] ) ) {
                switch ( Fnt_ProductListCons::$column_format[ $order_by ] ) {
                    case 'float':
                        self::$sql_args[ 'order_by' ] = " CONVERT({$wpdb->postmeta}.meta_value, DECIMAL(20,2))";
                        break;
                    case 'int':
                        self::$sql_args[ 'order_by' ] = " CONVERT({$wpdb->postmeta}.meta_value, SIGNED INTEGER)";
                        break;
                    case 'datetime':
                        self::$sql_args[ 'order_by' ] = " CONVERT({$wpdb->postmeta}.meta_value, DATETIME)";
                        break;
                    case 'string':
                        self::$sql_args[ 'order_by' ] = " {$wpdb->postmeta}.meta_value";
                        break;
                    default:
                        self::$sql_args[ 'order_by' ] = "";
                        break;
                }
            }
        }

        /**
         * Use to order product by data in table posts
         * @param $order_by
         */
        private static function get_order_by_wp_posts_field( $order_by ) {
            global $wpdb;
            if ( isset( Fnt_ProductListCons::$column_format[ $order_by ] ) && ! empty( Fnt_ProductListCons::$column_format[ $order_by ] ) ) {
                switch ( Fnt_ProductListCons::$column_format[ $order_by ] ) {
                    case 'float':
                        self::$sql_args[ 'order_by' ] = " CONVERT({$wpdb->posts}." . Fnt_ProductListCons::$column_name_in_db[ $order_by ] . ", DECIMAL(20,2))";
                        break;
                    case 'int':
                        self::$sql_args[ 'order_by' ] = " CONVERT({$wpdb->posts}." . Fnt_ProductListCons::$column_name_in_db[ $order_by ] . ", SIGNED INTEGER)";
                        break;
                    case 'datetime':
                        self::$sql_args[ 'order_by' ] = " CONVERT({$wpdb->posts}." . Fnt_ProductListCons::$column_name_in_db[ $order_by ] . ", DATETIME)";
                        break;
                    case 'string':
                        self::$sql_args[ 'order_by' ] = " {$wpdb->posts}." . Fnt_ProductListCons::$column_name_in_db[ $order_by ];
                        break;
                    default:
                        self::$sql_args[ 'order_by' ] = "";
                        break;
                }
            }
        }

        /**
         * @param $per_page
         * @param $page_number
         * @return string
         */
        private static function get_sql_when_default_way( $per_page, $page_number ) {
            global $wpdb;
            if ( self::is_in_trash_filter() ) {
                $not_in = " AND {$wpdb->posts}.post_status NOT IN ('auto-draft')";
            } else {
                $not_in = " AND {$wpdb->posts}.post_status NOT IN ('auto-draft', 'trash')";
            }

            $from = self::$sql_args[ 'from' ];

            $post_type = self::$url_args[ 'post_type' ];
            $wpdb->escape_by_ref($post_type);

            $where = self::$sql_args[ 'where' ];

            $sql = "SELECT {$wpdb->posts}.ID
                    FROM {$wpdb->posts} $from
                    WHERE {$wpdb->posts}.post_type = '$post_type' $where $not_in";

            if ( ! empty( self::$sql_args[ 'order_by' ] ) && ! empty( self::$sql_args[ 'order' ] ) ) {
                $order_by = self::$sql_args[ 'order_by' ];
                $order = self::$sql_args[ 'order' ];
                $sql .= sprintf( "\n ORDER BY %s %s", $order_by, $order );
            }

            $sql .= " LIMIT ". $per_page;
            $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

            return $sql;
        }

        /**
         * @return string
         */
        private static function get_sql_when_get_all_data() {
            global $wpdb;
            if ( self::is_in_trash_filter() ) {
                $not_in = " AND {$wpdb->posts}.post_status NOT IN ('auto-draft')";
            } else {
                $not_in = " AND {$wpdb->posts}.post_status NOT IN ('auto-draft', 'trash')";
            }

            $from = self::$sql_args[ 'from' ];

            $post_type = self::$url_args[ 'post_type' ];
            $wpdb->escape_by_ref($post_type);

            $where = self::$sql_args[ 'where' ];

            $sql = "SELECT {$wpdb->posts}.ID
                    FROM {$wpdb->posts} $from
                    WHERE {$wpdb->posts}.post_type = '$post_type' $where $not_in";

            if ( ! empty( self::$sql_args[ 'order_by' ] ) && ! empty( self::$sql_args[ 'order' ] ) ) {
                $order_by = self::$sql_args[ 'order_by' ];
                $order = self::$sql_args[ 'order' ];
                $sql .= sprintf( "\n ORDER BY %s %s", $order_by, $order );
            }

            return $sql;
        }

        /**
         * @return string
         */
        private static function get_sql_when_count_item() {
            global $wpdb;
            if ( self::is_in_trash_filter() ) {
                $not_in = "AND {$wpdb->posts}.post_status NOT IN ('auto-draft')";
            } else {
                $not_in = "AND {$wpdb->posts}.post_status NOT IN ('auto-draft', 'trash')";
            }

            $post_type = self::$url_args[ 'post_type' ];
            $wpdb->escape_by_ref($post_type);

            $sql = sprintf( "SELECT COUNT(DISTINCT {$wpdb->posts}.ID)
                             FROM  {$wpdb->posts} %s
                             WHERE {$wpdb->posts}.post_type = '%s' %s %s",
                             self::$sql_args[ 'from' ], $post_type, $not_in, self::$sql_args[ 'where' ] );

            return $sql;
        }

        public static function product_count( $status = 'all' ) {
            global $wpdb;
            self::$url_args = self::get_args_from_url();
            $wpdb->escape_by_ref($status);
            if ( $status == 'all' ) {
                $where = " AND post.post_status NOT IN ('auto-draft', 'trash')";
            } else {
                $where = sprintf( " AND post.post_status = '%s' AND post.post_status NOT IN ('auto-draft')", $status );
            }

            $post_type = isset(self::$url_args[ 'post_type' ]) && !empty(self::$url_args[ 'post_type' ]) ? self::$url_args[ 'post_type' ] : 'product';
            $wpdb->escape_by_ref($post_type);

            $sql = sprintf( "SELECT COUNT(DISTINCT post.ID)
                             FROM   {$wpdb->posts} post
                             WHERE  post.post_type = '%s' %s",
                             $post_type, $where );

            if ( self::is_just_import_product() ) {

                // investigating
//                "SELECT COUNT(DISTINCT ID) AS num_temp
//                                FROM $wpdb->posts main INNER JOIN $wpdb->postmeta pm on main.ID = pm.post_id
//                                WHERE post_type = 'product' AND pm.meta_key = '".FNT_IS_IMPORTING_META_KEY."' AND pm.meta_value = '1'"
                $sql = sprintf( "SELECT COUNT(DISTINCT post.ID)
                                 FROM   {$wpdb->posts} post INNER JOIN $wpdb->postmeta pm on post.ID = pm.post_id  
                                 WHERE post.post_type = 'product' AND pm.meta_key = '".FNT_IS_IMPORTING_META_KEY."' AND pm.meta_value = '1' 
                                 %s ", $where );

            }

            return $sql;
        }

        /**
         * Prepare sql query
         * @param string $url
         * @param string $query_type
         * @param int $per_page
         * @param int $page_number
         * @return string
         */
        public static function prepare_query( $query_type = 'count_item', $url = '', $per_page = LOADED_PRODUCTS_PER_PAGE, $page_number = 1 ) {
            global $wpdb;
            global $export_url;
            $export_url = $url;
            self::$url_args = self::get_args_from_url( $url );

            $order_by = isset( self::$url_args[ 'orderby' ] ) && ! empty( self::$url_args[ 'orderby' ] ) ? self::$url_args[ 'orderby' ] : 'ID';
            $order = isset( self::$url_args[ 'order' ] ) && ! empty( self::$url_args[ 'order' ] ) ? self::$url_args[ 'order' ] : 'DESC';

            $wpdb->escape_by_ref($order_by);
            $wpdb->escape_by_ref($order);

            self::$sql_args[ 'order_by' ] = $order_by;
            self::$sql_args[ 'order' ]    = $order;

            if ( isset( self::$url_args[ 's' ] ) ) {
                self::search_product( self::$url_args[ 's' ] );
            }
            if ( isset( self::$url_args[ 'm' ] ) ) {
                self::get_filter_by_month( self::$url_args[ 'm' ] );
            }
            if ( isset( Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_CATEGORIES ] ) && isset( self::$url_args[ Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_CATEGORIES ] ] ) ) {
                self::get_filter_by_category( self::$url_args[ Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_CATEGORIES ] ] );
            }
            if ( isset( Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ] ) && isset( self::$url_args[ Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ] ] ) ) {
                self::get_filter_by_type( self::$url_args[ Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_TYPE ] ] );
            }
            if ( isset( Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_STATUS ] ) && isset( self::$url_args[ Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_STATUS ] ] ) ) {
                self::get_filter_by_status( self::$url_args[ Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_STATUS ] ] );
            }

            if ( isset( Fnt_ProductListCons::$column_table_in_db[ self::$sql_args[ 'order_by' ] ] ) && ! empty( Fnt_ProductListCons::$column_table_in_db[ self::$sql_args[ 'order_by' ] ] ) ) {
                switch ( Fnt_ProductListCons::$column_table_in_db[ self::$sql_args[ 'order_by' ] ] ) {
                    case 'posts':
                        self::get_order_by_wp_posts_field( self::$sql_args[ 'order_by' ] );
                        break;
                    case 'postmeta':
                        self::$sql_args[ 'from' ]  .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id";
                        self::$sql_args[ 'where' ] .= sprintf("\n AND {$wpdb->postmeta}.meta_key = '%s'", Fnt_ProductListCons::$column_name_in_db[ self::$sql_args[ 'order_by' ] ]);
                        self::get_order_by_wp_postmeta_field( self::$sql_args[ 'order_by' ] );
                        break;
                    default:
                        break;
                }
            }

            // force post_type as "product" to fix for woocommerce 3.x
            // move processing import at here to avoid twice joining into postmeta table
            if ( self::is_just_import_product() ) {

                self::$url_args[ 'post_type' ] = 'product';
                // custom from and where for processing of importing products in here
                self::$sql_args[ 'from' ]  .= " INNER JOIN {$wpdb->postmeta} pm1 ON {$wpdb->posts}.ID = pm1.post_id AND pm1.`meta_key` = '".FNT_IS_IMPORTING_META_KEY."' AND pm1.`meta_value` = 1 ";
            }

            switch ( $query_type ) {
                case 'count_item':
                    $sql = self::get_sql_when_count_item();
                    break;
                case 'get_all_data':
                    $sql = self::get_sql_when_get_all_data();
                    break;
                default:
                    $sql = self::get_sql_when_default_way( $per_page, $page_number );
                    break;
            }

            // reset query var
            self::$sql_args = array(
                'from'      => '',
                'where'     => '',
                'order_by'  => '',
                'order'     => ''
            );

            return $sql;
        }




        /**
         * Check if in view list product temp
         * @return bool
         */
        public static function is_just_import_product() {
            if ( isset( $_GET[ 'just-import-product' ] ) && ! empty( $_GET[ 'just-import-product' ] ) ) {
                return true;
            }

            return false;
        }

        /*
         * get view mode to display list table(list or excerpt)
         */
        public static function get_mode() {
            return ( isset( $_GET[ 'mode' ] ) && ! empty( $_GET[ 'mode' ] ) ) ? $_GET[ 'mode' ] : 'list';
        }

        /*
         * get value is searching and display this for user
         */
        public static function get_search_result_label() {
            if ( isset( $_GET[ 's' ] ) && ! empty( $_GET[ 's' ] ) ) {
                $result = sprintf( 'Search results for “%s”', $_GET[ 's' ] );
            } else {
                $result = '';
            }

            return $result;
        }

        /*
         * Check if is being view trash list
         */
        public static function is_in_trash_filter() {
            if ( isset( $_GET[ 'post_status' ] ) && $_GET[ 'post_status' ] == 'trash' ) {
                return true;
            }

            return false;
        }

        /**
         * Check is on page edit attributes or not
         * @since 1.0.6
         * @return bool
         */
        public static function is_attributes_page() {
            if ( isset( $_GET['edit-attributes'] ) ) {
                return true;
            }
            return false;
        }
    }