<?php
    require_once FNT_DIR_CORE_INCLUDES . '/abstract.php';

    class Fnt_ProductListManagement extends Fnt_Core {
        static $instance;

        // customer WP_List_Table object
        public $products_obj;

        public function __construct() {
            add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-factory-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-simple-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-grouped-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-external-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-variable-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-variation-custom.php';
            require_once FNT_DIR_PRODUCT_INCLUDES . '/wc-product-temp-custom.php';

            if ( ! class_exists( 'WP_List_Table' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
            }
            require_once FNT_DIR_CORE_INCLUDES . '/product-list-table.php';
        }

        public static function set_screen( $status, $option, $value ) {
            return $value;
        }

        public function screen_option() {
            /**
             * @since ver 1.0.6
             * If is edit attributes page, not need screen option tab
             */
            if ( Fnt_Url_Handler::is_attributes_page() ) {
                return;
            }

            $option = 'per_page';
            $args = array(
                'label'   => __( 'Number of items per page:', 'fnt' ),
                'default' => LOADED_PRODUCTS_PER_PAGE,
                'option'  => 'products_per_page'
            );
            add_screen_option( $option, $args );
            $this->products_obj = new Fnt_CustomProductList();
        }
    }