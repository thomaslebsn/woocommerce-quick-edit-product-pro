<?php
/**
 * Plugin Name: WooCommerce Bulk Edit Products Premium
 * Plugin URI: http://codecanyon.net/item/woocommerce-bulk-edit-products-premium
 * Description: This plugin help us to interact as easy as with the products by the following actions, such as add, edit, delete multiple products and much more.
 * Version: 1.0.0
 * Author: ThomasLe
 * Author URI: http://thomaslebsn.info
 * Developer: ThomasLe
 * Developer URI: http://thomaslebsn.info/
 * Text Domain: woocommerce-bulk-edit-product-premium
 * Domain Path: /languages
 *
 * Copyright: © 2020 ThomasLe - All rights reserved.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
ob_start();
// define path for directories
$base = dirname(__FILE__);
$wp_load_path = dirname(dirname(dirname($base)));
require_once($wp_load_path . "/wp-load.php");
require_once( $wp_load_path . '/wp-admin/includes/admin.php' );
require_once( $wp_load_path . '/wp-admin/includes/plugin.php' );
define('FNT_PLUGIN_NAME','<b>Woocommerce Quick Edit Products Pro Plugin</b>');
define('FNT_DIR_PLUGIN', $base);
define('FNT_DIR_PROJECT', $wp_load_path);
define('FNT_URL_PLUGIN',plugin_dir_url(__FILE__));
define('FNT_PLUGIN_BASENAME',plugin_basename(__FILE__));
define('FNT_QEPP_VERSION', '1.1.0');
define('FNT_DIR_CORE_INCLUDES', FNT_DIR_PLUGIN . '/core-includes');
define('FNT_DIR_PRODUCT_INCLUDES', FNT_DIR_PLUGIN . '/product-includes');
define('FNT_DIR_EXCEL_INCLUDES',FNT_DIR_PLUGIN . '/excel-includes');
define('FNT_DIR_TEMPLATE',FNT_DIR_PLUGIN . '/templates');
define('FNT_DIR_LIBRARY',FNT_DIR_PLUGIN . '/libs');
define('FNT_DIR_ASSETS',FNT_DIR_PLUGIN . '/assets');
$upload_dir = wp_upload_dir();
define('FNT_DIR_UPLOAD_BASE', $upload_dir['basedir']);
define('FNT_URL_UPLOAD_BASE', $upload_dir['baseurl']);

//Required functions - require all file into /includes/*
require_once($base . '/main-process.php');
function fnt_start_process_when_plugin_loaded(){
    $fnt_fnt = new Fnt_QEPP();
    $fnt_fnt->run();
}
// check case is load page admin in popup iframe, add style for this page
function fnt_add_styles_for_popup_iframe(){
    if(isset($_GET['fnt_iframe_popup']) && $_GET['fnt_iframe_popup'] == 1) {
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
                add_filter( 'mce_buttons', 'fnt_remove_fullscreen_mce_buttons_wp40', 999 );
                wp_enqueue_style('styles-for-popup-iframe-wp4.0', FNT_URL_PLUGIN . 'assets/css/styles-for-popup-iframe-wp4.0.css', false, FNT_QEPP_VERSION, 'all');
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
                add_filter( 'mce_buttons', 'fnt_remove_fullscreen_mce_buttons', 999 );
                wp_enqueue_style('styles-for-popup-iframe-wp4.1', FNT_URL_PLUGIN . 'assets/css/styles-for-popup-iframe-wp4.1.css', false, FNT_QEPP_VERSION, 'all');
                break;
            default:
                add_filter( 'mce_buttons', 'fnt_remove_fullscreen_mce_buttons', 999 );
                wp_enqueue_style('styles-for-popup-iframe', FNT_URL_PLUGIN . 'assets/css/styles-for-popup-iframe.css', false, FNT_QEPP_VERSION, 'all');
        }
    }
}

/**
 * Remove button view fullscreen of short description editor from the core button that's disabled by default
 */
function fnt_remove_fullscreen_mce_buttons( $buttons ) {
    foreach ( $buttons as $key => $value ) {
        // if is button view fullscreen, unset this from array buttons
        if ( $value == 'fullscreen' ) {
            unset( $buttons[ $key ] );
        }
    }
    return $buttons;
}

function fnt_remove_fullscreen_mce_buttons_wp40( $buttons ) {
    // if wp version is 4.0, then just remove button viewfullscreen of short description editor
    static $editor_index = 0;
    if ( $editor_index == 1 ) {
        foreach ( $buttons as $key => $value ) {
            if ( $value == 'fullscreen' ) {
                unset( $buttons[ $key ] );
            }
        }
    }
    $editor_index = $editor_index == 0 ? 1 : 0;
    return $buttons;
}

function fnt_admin_notices() {
    $notices= get_option(Fnt_QEPP::FNT_ADMIN_NOTICE_MESSAGE_KEY, array());
    if ( !empty($notices) ) {
        foreach ($notices as $notice) {
            echo "<div class='error'><p>".$notice."</p></div>";
        }
        delete_option(Fnt_QEPP::FNT_ADMIN_NOTICE_MESSAGE_KEY);
    }
}
add_action( 'admin_notices', 'fnt_admin_notices');
// If plugin installed on Wordpress have multisite actived, the compatibility function working not correctly.
// So check if site is multi, then do not call function check compatibility.
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
    register_activation_hook( __FILE__, array( 'Fnt_QEPP', 'install' ) );
    register_deactivation_hook( __FILE__, array( 'Fnt_QEPP', 'uninstall' ) );
} else { // If not is multi site
    if(Fnt_QEPP::compatibility_checker()){
        register_activation_hook( __FILE__, array( 'Fnt_QEPP', 'install' ) );
        register_deactivation_hook( __FILE__, array( 'Fnt_QEPP', 'uninstall' ) );
    } else {
        Fnt_QEPP::self_deactivate_plugin();
        $_GET['deactivate'] = 1;
        unset($_GET['activate']);
        Fnt_QEPP::add_admin_notice_message(FNT_PLUGIN_NAME . __(' could not be activated or deactivated automatically. The wordpress and woocommerce versions are not compatible with this plugin. <br/> Wordpress version requirement : >= '.Fnt_QEPP::COMPATIBILITY_WP_VERSION.' <br/> Woocommerce plugin version requirement : >= '.Fnt_QEPP::COMPATIBILITY_WOOCOMMERCE_VERSION.' <br/> Please double-check compatibility version of dependencies and re-active the ' . FNT_PLUGIN_NAME,'fnt'));
    }
}

$is_fnt_edit_page = isset( $_GET['page'] ) && $_GET['page'] == 'bulk-edit-products-premium-submenu-page';
$is_fnt_ajax_get_call = isset( $_GET['action'] ) && $_GET['action'] == 'fnt_product_manage';
$is_fnt_ajax_post_call = isset( $_POST['action'] ) && $_POST['action'] == 'fnt_product_manage';
if ( ( $is_fnt_edit_page || $is_fnt_ajax_get_call || $is_fnt_ajax_post_call ) && class_exists( 'Fnt_QEPP' ) ) {
    add_action( 'plugins_loaded', 'fnt_start_process_when_plugin_loaded');
    // Since ver 1.0.5
    // If woocommerce version >= 2.6.0, apply this filter to add class name type 'temp'
    if ( version_compare( Fnt_QEPP::get_woo_version_number(), '2.6.0', '>=' ) ) {
        add_filter( 'woocommerce_product_class', 'fnt_get_product_class', 10, 4 );
    }
} elseif( class_exists( 'Fnt_QEPP' ) ) {
    $fnt_fnt = new Fnt_QEPP();
    add_action( 'admin_head', 'fnt_add_styles_for_popup_iframe' );
    add_action( 'admin_menu', array( $fnt_fnt, 'register_quick_edit_product_pro_submenu_page' ) );
}

/**
 * Since ver 1.0.5
 * Fix compatibility with Woocommerce ver 2.6.1
 * Get the product class name, add class name for product type 'temp'
 * @return string
 */
function fnt_get_product_class( $classname, $product_type, $post_type, $product_id ) {
    if ( 'temp' === $post_type ) {
        $terms        = get_the_terms( $product_id, 'product_type' );
        $product_type = ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';

        $classname = fnt_get_classname_from_product_type( $product_type );
        // Filter classname so that the class can be overridden if extended.
        return apply_filters( 'fnt_woocommerce_product_class', $classname, $product_type, $post_type, $product_id );
    }
    return $classname;
}
function fnt_get_classname_from_product_type( $product_type ) {
    return $product_type ? 'WC_Product_' . implode( '_', array_map( 'ucfirst', explode( '-', $product_type ) ) ).'_Custom': false;
}