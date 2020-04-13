<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Fnt_QEPP{
    const INSTALLED_PLUGIN = 1;
    const UNINSTALLED_PLUGIN = 0;
    const COMPATIBILITY_WP_VERSION = '4.0';
    const COMPATIBILITY_WOOCOMMERCE_VERSION = '3.0.0';
    const FNT_ADMIN_NOTICE_MESSAGE_KEY = 'fnt_determined_plugin_notice';

    protected $product_list_management_class = null;
    protected $product_action_management_class = null;
    protected $page_hook_name = null;
    protected $preserved_markup_editor_class = null;

    public function __construct(){
    }
    static function install(){
        add_option('fnt-qepp',self::INSTALLED_PLUGIN);
    }

    static function uninstall(){
        delete_option('fnt-qepp');
    }

    static function self_deactivate_plugin(){
        try{
            deactivate_plugins(FNT_PLUGIN_BASENAME);
            self::uninstall();
        } catch(Exception $ex){}
    }

    static function get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if ( ! function_exists( 'get_plugins' ) )
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';

        // If the plugin version number is set, return it
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
            return $plugin_folder[$plugin_file]['Version'];

        } else {
            // Otherwise return null
            return NULL;
        }
    }

    static function add_admin_notice_message($message){
        $current_messages = get_option(self::FNT_ADMIN_NOTICE_MESSAGE_KEY,array());
        if(!in_array($message,$current_messages)){
            $current_messages[] = $message;
            update_option(self::FNT_ADMIN_NOTICE_MESSAGE_KEY,$current_messages);
        }
    }

    static function get_admin_notice_message(){
        return get_option(self::FNT_ADMIN_NOTICE_MESSAGE_KEY,array());
    }

    static function compatibility_checker(){
        return (
            in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
            && version_compare( get_bloginfo('version'), self::COMPATIBILITY_WP_VERSION, '>=' )
            && version_compare( self::get_woo_version_number(), self::COMPATIBILITY_WOOCOMMERCE_VERSION, '>=' )
        );
    }

    static function plugin_activated_checker(){
        return in_array( 'quick-edit-products-pro/quick-edit-products-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    }

    public function run(){
        $is_plugin_installed = get_option('fnt-qepp',self::UNINSTALLED_PLUGIN);
        if($is_plugin_installed != self::INSTALLED_PLUGIN) {
            self::add_admin_notice_message(FNT_PLUGIN_NAME . __(' was not installed correctly. Please reactivate this plugin to resolve this problem.','fnt'));
            return;
        }
        // load dependencies to run correctly functions of the plugin
        require_once(FNT_DIR_CORE_INCLUDES . '/' . 'define-constant.php');
        require_once(FNT_DIR_CORE_INCLUDES . '/' . 'product-list-management.php');
        require_once(FNT_DIR_CORE_INCLUDES . '/' . 'product-action-management.php');
        require_once(FNT_DIR_CORE_INCLUDES . '/' . 'debug.php');
        require_once(FNT_DIR_CORE_INCLUDES . '/' . 'logs.php');

        if(class_exists('Fnt_ProductListManagement')
            && class_exists('Fnt_ProductActionManagement')
        ){
            $this->product_action_management_class = new Fnt_ProductActionManagement();
            $this->product_list_management_class = new Fnt_ProductListManagement();

            add_action( 'wp_ajax_fnt_product_manage', array($this->product_action_management_class, 'action_controller_callback') );
            add_action( 'wp_ajax_nopriv_fnt_product_manage', array($this->product_action_management_class, 'action_controller_callback') );
            // enqueue scripts
            add_action('admin_enqueue_scripts', array($this, 'fnt_include_backend_scripts'), 20 );
            add_action('admin_menu', array($this, 'register_quick_edit_product_pro_submenu_page'));
        }
    }
    public function register_quick_edit_product_pro_submenu_page() {
            $this->page_hook_name = add_submenu_page( 'edit.php?post_type=product',
                'Quick Edit Pro',
                'Quick Edit Product Pro',
                'manage_product_terms',
                'quick-edit-product-pro-submenu-page',
                array($this,'quick_edit_product_pro_submenu_page_callback')
            );
            if(class_exists('Fnt_ProductListManagement') && !empty($this->product_list_management_class)){
                $this->product_list_management_class->set_page_hook_name($this->page_hook_name);
                add_action( "load-".$this->page_hook_name, array($this->product_list_management_class, 'screen_option') );
            }
    }

    public function quick_edit_product_pro_submenu_page_callback() {
        $action = (isset($_GET['fnt_action']) && !empty($_GET['fnt_action'])) ? $_GET['fnt_action'] : "";
        if(empty($action) && isset($_POST['product-form-submit'])) {
            $action = "submit-form";
        }
        switch ($action) {
            case "submit-form":
                // cpr : current-products-results - containing all query string on previous url
                if(!empty($_POST['products'])) {
                    $productsList = $_POST['products'];
                    foreach($productsList as $productItem){
                        $this->add_product($productItem);
                    }
                }
                wp_redirect(self::get_redirect_page_url());
                break;
            default:
                // for example
                $add_product_data_init = $this->product_management_get_default_post_to_edit('product',array());
                // $qs_args : query string arguments
                if(isset($_GET['page']) && !empty($_GET['page'])){
                    $page = $_GET['page'];
                } else {
                    $page = "quick-edit-product-pro-submenu-page";
                }

                if(isset($_GET['post_status']) && !empty($_GET['post_status'])){
                    $post_status = $_GET['post_status'];
                } else {
                    $post_status = "";
                }

                if(isset($_GET['post_type']) && !empty($_GET['post_type'])){
                    $post_type = $_GET['post_type'];
                } else {
                    $post_type = "product";
                }
                $add_product_backbone_modal_box = self::hm_get_template_part(FNT_DIR_TEMPLATE . '/template-backbone-modals.php', array(
                    'page_hook_name' => $this->page_hook_name,
                    'add_product_data_init' => $add_product_data_init
                ));

                $settings_backbone_modal_box = self::hm_get_template_part(FNT_DIR_TEMPLATE . '/template-settings-modal.php', array(
                    'page_hook_name' => $this->page_hook_name
                ));

                echo self::hm_get_template_part(FNT_DIR_TEMPLATE . '/template-product-list.php',
                    array('products_obj' => $this->product_list_management_class->products_obj,
                        'add_products_backbone_modal_box' => $add_product_backbone_modal_box,
                        'settings_backbone_modal_box' => $settings_backbone_modal_box,
                        'page_hook_name' => $this->page_hook_name,
                        'qs_page' => $page, // param $page inner query string on url
                        'post_status' => $post_status, // param $post_status inner query string on url
                        'qs_post_type' => $post_type, // param $post_type inner query string on url
                        'add_product_data_init' => $add_product_data_init
                    )
                );
                break;

        }
    }

    private function get_all_category($multiple = true){
        $args = array(
            'show_option_all'    => 'Select a category',
            'show_option_none'   => '',
            'option_none_value'  => '-1',
            'orderby'            => 'ID',
            'order'              => 'ASC',
            'show_count'         => 0,
            'hide_empty'         => 0,
            'child_of'           => 0,
            'exclude'            => '',
            'echo'               => 0,
            'selected'           => 0,
            'hierarchical'       => 1,
            'name'               => '%name%',
            'id'                 => '%id%',
            'class'              => '%classes%',
            'depth'              => 0,
            'tab_index'          => 0,
            'taxonomy'           => 'product_cat',
            'hide_if_empty'      => false,
            'value_field'	     => 'term_id',
        );
        $result = wp_dropdown_categories( $args );
        if($multiple){
            $result = str_replace( 'id=', 'multiple="multiple" id=', $result );
        }
        return $result;
    }

    public function fnt_include_backend_scripts(){
        global $wp_scripts;
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $screen = get_current_screen();

        if($screen->id !== $this->page_hook_name){
            return;
        }
        /**
         * @since ver 1.0.6
         * Use to load needed script for page attributes, edit products page
         */
        if ( Fnt_Url_Handler::is_attributes_page() ) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');
            wp_enqueue_script( 'fnt_woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin' . $suffix . '.js', array( 'jquery-tiptip' ), FNT_QEPP_VERSION, true );
            wp_enqueue_style( 'fnt_custom_admin_stylesheet', FNT_URL_PLUGIN . '/assets/css/custom-admin.css', array(), FNT_QEPP_VERSION );
        } else {
            wp_enqueue_style( 'fnt_stylesheets', FNT_URL_PLUGIN . 'assets/css/stylesheet.css', false, FNT_QEPP_VERSION, 'all' );
            wp_enqueue_style( 'fnt_backbone_modal_css', FNT_URL_PLUGIN . 'assets/css/stylesheet-backbone-modal.css', false, FNT_QEPP_VERSION, 'all' );
            wp_enqueue_script( 'fnt_core_js', FNT_URL_PLUGIN . 'assets/js/core.js', array(), FNT_QEPP_VERSION, true );
            wp_localize_script( 'fnt_core_js', 'initialize_variables',
                                array(
                                    'processing-image' => FNT_URL_PLUGIN . 'assets/images/loading-icon.gif',
                                    'wrap_image' => '%wrap_image%
                                    %remove_image_default%',
                                    'plugin_base_url' => self::get_redirect_page_url(),
                                    'fnt-setting-data' => get_option( 'fnt-settings-data', '' ),
                                    'weight_unit' => Fnt_Core::get_weight_unit(),
                                    'dimension_unit' => Fnt_Core::get_dimension_unit(),
                                    'message_show' => Fnt_ProductListCons::get_message_show(),
                                    'list_attributes' => Fnt_Core::get_product_attributes(),
                                    'list_attributes_dropdow' => Fnt_Core::get_dropdown_list_attributes(),
                                    'list_all_attributes' => Fnt_Core::get_all_list_attributes(),
                                )
            );
            wp_enqueue_script( 'fnt_backform_js', FNT_URL_PLUGIN . 'assets/js/includes/backform.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_add_product_modal_js', FNT_URL_PLUGIN . 'assets/js/add-products-modal.js', array(
                'jquery',
                'backbone',
                'underscore',
                'wp-util'
            ), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_extra_form_js', FNT_URL_PLUGIN . 'assets/js/backform/form-simple-product.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_settings_modal_js', FNT_URL_PLUGIN . 'assets/js/settings-modal.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_wp_editor_modal_js', FNT_URL_PLUGIN . 'assets/js/wp-editor-modal.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_settings_submission_js', FNT_URL_PLUGIN . 'assets/js/settings.js', array(), FNT_QEPP_VERSION, true );
            wp_localize_script( 'fnt_add_product_modal_js', 'meta_image',
                                array(
                                    'title' => __( 'Choose or Upload an Image', 'prfx-textdomain' ),
                                    'button' => __( 'Use this image', 'prfx-textdomain' ),
                                )
            );
            wp_localize_script( 'fnt_add_product_modal_js', 'default_product_fields',
                                array(
                                    'simple' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'simple' ] ),
                                    'grouped' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'grouped' ] ),
                                    'external' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'external' ] ),
                                    'variable' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'variable' ] ),
                                )
            );

            wp_localize_script( 'fnt_extra_form_js', 'data_product_default',
                                array(
                                    'category' => $this->get_all_category(),
                                    'product_tags' => 'aloha',
                                    'simple_product_fields' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'simple' ] ),
                                    'grouped_product_fields' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'grouped' ] ),
                                    'external_product_fields' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'external' ] ),
                                    'variable_product_fields' => array_merge( Fnt_ProductListCons::$product_mapping_js[ 'default' ], Fnt_ProductListCons::$product_mapping_js[ 'variable' ] ),
                                )
            );

            wp_enqueue_script( 'fnt_product_list_handler', FNT_URL_PLUGIN . 'assets/js/product-list-handler.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_add_product_modal_handler', FNT_URL_PLUGIN . 'assets/js/add-products-modal.js', array(), FNT_QEPP_VERSION, true );
            // enqueue script bootstrap for backform
            wp_enqueue_script( 'fnt_bootstrap_js', FNT_URL_PLUGIN . 'assets/bootstrap/fnt-bootstrap.min.js', array(), FNT_QEPP_VERSION, true );
            // enqueue script bootstrap input file for backform
            wp_enqueue_script( 'fnt_bootstrap_input_file_js', FNT_URL_PLUGIN . 'assets/bootstrap/fnt-bootstrap-file-input.js', array(), FNT_QEPP_VERSION, true );
            // enqueue stylesheet bootstrap for backform
            wp_enqueue_style( 'fnt_bootstrap_css', FNT_URL_PLUGIN . 'assets/bootstrap/fnt-bootstrap.css', false, FNT_QEPP_VERSION, 'all' );
            // enqueue stylesheet bootstrap for backform
            wp_enqueue_style( 'fnt_bootstrap_color_picker_css', FNT_URL_PLUGIN . 'assets/bootstrap/colorpicker/css/bootstrap-colorpicker.min.css', false, FNT_QEPP_VERSION, 'all' );
            wp_enqueue_script( 'fnt_bootstrap_color_picker_js', FNT_URL_PLUGIN . 'assets/bootstrap/colorpicker/js/bootstrap-colorpicker.min.js', array(), FNT_QEPP_VERSION, true );

            // enqueue script of backform
            wp_enqueue_script( 'fnt_backform_extra_form_control', FNT_URL_PLUGIN . 'assets/js/backform/extra-form-controls.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_backform_add_new_simple_product', FNT_URL_PLUGIN . 'assets/js/backform/form-simple-product.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_backform_add_new_grouped_product', FNT_URL_PLUGIN . 'assets/js/backform/form-grouped-product.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_backform_add_new_external_product', FNT_URL_PLUGIN . 'assets/js/backform/form-external-product.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_backform_add_new_variable_product', FNT_URL_PLUGIN . 'assets/js/backform/form-variable-product.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_backform_excel_template_setting', FNT_URL_PLUGIN . 'assets/js/backform/form-excel-template-setting.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_backform_settings', FNT_URL_PLUGIN . 'assets/js/backform/form-settings.js', array(), FNT_QEPP_VERSION, true );

            //enqueue script of auto-complete text
            wp_enqueue_script( 'suggest' );

            //enqueue script numeral.js to handle currency formatting on this plugin
            wp_register_script( 'fnt_numeral_library', FNT_URL_PLUGIN . 'assets/js/includes/numeral.js', array(), FNT_QEPP_VERSION, true );

            // enqueue script numeral-handler.js and global variable currency_format for it.
            wp_enqueue_script( 'fnt_numeral_handler', FNT_URL_PLUGIN . 'assets/js/numeral-handler.js', array( 'fnt_numeral_library' ), FNT_QEPP_VERSION, true );
            wp_localize_script( 'fnt_numeral_handler', 'currency_format',
                                array(
                                    'currency_format_num_decimals' => wc_get_price_decimals(),
                                    'currency_format_symbol' => get_woocommerce_currency_symbol(),
                                    'currency_format_decimal_sep' => esc_attr( wc_get_price_decimal_separator() ),
                                    'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
                                    'currency_position' => esc_attr( get_option( 'woocommerce_currency_pos', 'left' ) )
                                )
            );

            // enqueue stylesheet woocommerce admin.css for this plugin to serve for product list table RWD
            wp_enqueue_style( 'fnt_custom_admin_stylesheet', FNT_URL_PLUGIN . '/assets/css/custom-admin.css', array(), FNT_QEPP_VERSION );
            //enqueue script and stylesheet of validation backform
            wp_enqueue_script( 'fnt_validation_backform', FNT_URL_PLUGIN . 'assets/js/validation-engine-master/jquery.validationEngine.js', array(), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_validation_en_backform', FNT_URL_PLUGIN . 'assets/js/validation-engine-master/languages/jquery.validationEngine-en.js', array(), FNT_QEPP_VERSION, true );

            wp_enqueue_style( 'fnt_validation_stylesheet', FNT_URL_PLUGIN . '/assets/css/validationEngine.jquery.css', array(), FNT_QEPP_VERSION );
            // enqueue script of marked input
            wp_enqueue_script( 'fnt_masked_input', FNT_URL_PLUGIN . 'assets/js/masked-input/inputmask.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_jquery_masked_input', FNT_URL_PLUGIN . 'assets/js/masked-input/jquery.inputmask.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_jquery_masked_money_input', FNT_URL_PLUGIN . 'assets/js/masked-input/jquery.maskMoney.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_jquery_masked_extensions', FNT_URL_PLUGIN . 'assets/js/masked-input/inputmask.extensions.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_jquery_masked_dependencies_lib', FNT_URL_PLUGIN . 'assets/js/masked-input/inputmask.dependencyLib.jquery.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_jquery_masked_numeric_input', FNT_URL_PLUGIN . 'assets/js/masked-input/inputmask.numeric.extensions.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            //enqueue script of bootbox.js
            wp_enqueue_script( 'fnt_boot_box_js', FNT_URL_PLUGIN . 'assets/js/bootbox/bootbox.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            //enqueue script of popup message
            wp_enqueue_script( 'fnt_popup_message_js', FNT_URL_PLUGIN . 'assets/js/popup-message.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            //enqueue script of TableScrollHandler
            wp_enqueue_script( 'fnt_table_scroll_handler_js', FNT_URL_PLUGIN . 'assets/js/table-scroll-handler.js', array( 'jquery' ), FNT_QEPP_VERSION, true );

            /* Enqueue scripts to solve to compatible browsers */
            wp_enqueue_script( 'fnt_compatible_brs_safari', FNT_URL_PLUGIN . 'assets/js/compatible-browsers/safari.js', array( 'jquery' ), FNT_QEPP_VERSION, true );
            /* Enqueue scripts to get data from form */
            wp_enqueue_script( 'fnt_serialize_object', FNT_URL_PLUGIN . 'assets/js/serialize-object/jquery.serialize-object.min.js', array( 'jquery' ), FNT_QEPP_VERSION, true );

            // since 1.1 to edit variation product
            wp_enqueue_script( 'fnt_product_variation_list_handler', FNT_URL_PLUGIN . 'assets/js/product-variation-handler.js', false, FNT_QEPP_VERSION, true );
            // since 1.1 to sort table row
            wp_enqueue_script( 'fnt_table_row_sorter', FNT_URL_PLUGIN . 'assets/js/row-sorter/row-sorter.min.js', false, FNT_QEPP_VERSION, true );
            wp_enqueue_script( 'fnt_table_row_sorter_handler', FNT_URL_PLUGIN . 'assets/js/table-row-sorter-handler.js', false, FNT_QEPP_VERSION, true );
            // process for product variable
            wp_enqueue_script( 'fnt_product_variable_handler', FNT_URL_PLUGIN . 'assets/js/product-variable-handler.js', false, FNT_QEPP_VERSION, true );
            /**
             * since ver 1.0.4
             * Script for select product categories
             */
            wp_enqueue_script( 'fnt_product_cat_selection_handler', FNT_URL_PLUGIN . 'assets/js/categories-select-handler.js', false, FNT_QEPP_VERSION, true );

            // Enqueue styles for compatibility version of wordpress
            global $wp_version;
            switch ( $wp_version ) {
                case '4.0':
                case '4.0.1':
                case '4.0.2':
                case '4.0.3':
                case '4.0.4':
                case '4.0.5':
                case '4.0.6':
                case '4.0.7':
                case '4.0.8':
                case '4.0.9':
                    wp_enqueue_style( 'fnt_stylesheets_wp4.0', FNT_URL_PLUGIN . 'assets/css/styles-for-wp4.0.css', false, FNT_QEPP_VERSION, 'all' );
                    break;
                case '4.1':
                case '4.1.1':
                case '4.1.2':
                case '4.1.3':
                case '4.1.4':
                case '4.1.5':
                case '4.1.6':
                case '4.1.7':
                case '4.1.8':
                case '4.1.9':
                    wp_enqueue_style( 'fnt_stylesheets_wp4.1', FNT_URL_PLUGIN . 'assets/css/styles-for-wp4.1.css', false, FNT_QEPP_VERSION, 'all' );
            }
        }
    }

    public static function hm_get_template_part( $file, $template_args = array(), $return = true, $cache_args = array() ) {

        $template_args = wp_parse_args( $template_args );
        $cache_args = wp_parse_args( $cache_args );

        if ( $cache_args ) {

            foreach ( $template_args as $key => $value ) {
                if ( is_scalar( $value ) || is_array( $value ) ) {
                    $cache_args[$key] = $value;
                } else if ( is_object( $value ) && method_exists( $value, 'get_id' ) ) {
                    $cache_args[$key] = call_user_method( 'get_id', $value );
                }
            }

            if ( ( $cache = wp_cache_get( $file, serialize( $cache_args ) ) ) !== false ) {

                if ( ! empty( $template_args['return'] ) )
                    return $cache;

                echo $cache;
                return;
            }

        }

        $file_handle = $file;

        do_action( 'start_operation', 'hm_template_part::' . $file_handle );

        if ( file_exists( get_stylesheet_directory() . '/' . $file . '.php' ) ) {
            $file = get_stylesheet_directory() . '/' . $file . '.php';
        }elseif ( file_exists( get_template_directory() . '/' . $file . '.php' ) ){
            $file = get_template_directory() . '/' . $file . '.php';
        }

        ob_start();
        $return = require( $file );
        $data = ob_get_clean();

        do_action( 'end_operation', 'hm_template_part::' . $file_handle );

        if ( $cache_args ) {
            wp_cache_set( $file, $data, serialize( $cache_args ), 3600 );
        }


        if ( $return )
            return $data;
        else
            echo $data;
    }

    public static function get_current_admin_url(){
        global $wp;
        return esc_url(add_query_arg( $_SERVER['QUERY_STRING'], '', admin_url( '/edit.php' ) ));
    }

    public static function decode_current_admin_url(){
        $current_url = (isset($_GET['pu']) && !empty($_GET['pu'])) ? $_GET['pu'] : "";
        $current_url = base64_decode($current_url);
        $current_url = esc_url($current_url);
        if(strpos($current_url,'#038;') !== false){
            $current_url = str_replace( '#038;', '&', $current_url );
        }
        if(strpos($current_url,'&&') !== false){
            $current_url = str_replace('&&', '&', $current_url);
        }
        return $current_url;
    }

    public static function custom_parse_str( $url_string = '' ) {
        // &amp;
        if(empty($url_string)) return null;
        if(strpos($url_string, '&amp;') !== false){
            $url_string = html_entity_decode($url_string);
        }
        $extracted_args = array();
        parse_str(parse_url( $url_string, PHP_URL_QUERY), $extracted_args);
        return $extracted_args;
    }

    public static function get_redirect_page_url($query_string_array = array()){
        $base_url = sprintf( "?post_type=product&page=%s",
            esc_attr( $_REQUEST['page'] )
        );
        if(!empty($query_string_array)){
            $array_item_query_string = array();
            foreach($query_string_array as $key => $value){
                $array_item_query_string[] = strtolower(str_replace(' ','_',$key)) . '=' . $value;
            }
            $base_url .= '&' . implode('&', $array_item_query_string);
        }
        return $base_url;
    }

    private function product_management_get_default_post_to_edit( $post_type = 'post', $postData=array(), $create_in_db = false ) {
        global $wpdb;

        $post_title = '';
        if ( !empty( $postData['post_title'] ) )
            $post_title = $postData['post_title'];

        $post_content = '';
        if ( !empty( $postData['post_content'] ) )
            $post_content = $postData['post_content'];

        $post_excerpt = '';
        if ( !empty( $postData['post_excerpt'] ) )
            $post_excerpt = $postData['post_excerpt'];

        $post_status = 'auto-draft';
        if ( !empty( $postData['post_status'] ) )
            $post_status = $postData['post_status'];

        if ( $create_in_db ) {
            $post_id = wp_insert_post( array( 'post_title' => $post_title,
                'post_type' => $post_type,
                'post_status' => $post_status,
                'post_content' => $post_content,
                'post_excerpt' => $post_excerpt,
                'comment_status' => 'closed' ) );
            $post = get_post( $post_id );
            if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) && get_option( 'default_post_format' ) )
                set_post_format( $post, get_option( 'default_post_format' ) );
        } else {
            $post = new stdClass;
            $post->ID = 0;
            $post->post_author = '';
            $post->post_date = '';
            $post->post_date_gmt = '';
            $post->post_password = '';
            $post->post_type = $post_type;
            $post->post_status = 'draft';
            $post->to_ping = '';
            $post->pinged = '';
            $post->comment_status = get_option( 'default_comment_status' );
            $post->ping_status = get_option( 'default_ping_status' );
            $post->post_pingback = get_option( 'default_pingback_flag' );
            $post->post_category = get_option( 'default_category' );
            $post->page_template = 'default';
            $post->post_parent = 0;
            $post->menu_order = 0;
            $post = new WP_Post( $post );
        }
        return $post;
    }

}