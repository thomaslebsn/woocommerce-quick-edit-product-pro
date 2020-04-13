<?php
require_once FNT_DIR_CORE_INCLUDES . '/abstract.php';
class Fnt_ProductActionManagement extends Fnt_Core {
    public function __construct(){}

    private function autocomplete_product_tag($posted_data = '') {
        $args = array(
            'orderby'           => 'name',
            'order'             => 'ASC',
            'hide_empty'        => true,
            'exclude'           => array(),
            'exclude_tree'      => array(),
            'include'           => array(),
            'number'            => '',
            'fields'            => 'all',
            'slug'              => '',
            'parent'            => '',
            'hierarchical'      => true,
            'child_of'          => 0,
            'childless'         => false,
            'get'               => '',
            'name__like'        => '',
            'description__like' => '',
            'pad_counts'        => false,
            'offset'            => '',
            'search'            => '',
            'cache_domain'      => 'core'
        );

        $product_tag = get_terms(array('product_tag'), $args);
        $array_sender = array();
        foreach($product_tag as $item){
            $pos = strpos($item->name, $posted_data['search_text']);
            if( $pos !== false){
                array_push($array_sender,$item);
            }
        }
        return $this->response_handler(true,array('message'=> json_encode($product_tag,true)));
    }
    private function multiple_edit_inline($posted_data = array(), &$error_array = array()){
        $array_edit = array();
        $array_add = array();
        if(!empty($posted_data) && is_array($posted_data) ){
            foreach($posted_data as $item) {
                if($item[Fnt_ProductListCons::COLUMN_ID] > 0){
                    array_push($array_edit,$item);
                }
                if($item[Fnt_ProductListCons::COLUMN_ID] < 0){
                    array_push($array_add,$item);
                }
            }
            WC_Product_Custom::update_products($array_edit, $error_array);
            return true;
        }
        return false;
    }
    private function check_sku_not_exists($posted_data){
        global $wpdb;
        $sku = isset($posted_data['data']['sku']) ? $posted_data['data']['sku'] : '';
//        $post_type = isset($posted_data['data']['post_type']) ? $posted_data['data']['post_type'] : '';
        $old_sku = isset($posted_data['data']['old_sku']) ? $posted_data['data']['old_sku'] : '';
        $product_id = isset($posted_data['data']['product_id']) ? $posted_data['data']['product_id'] : '';
        try{
            if(empty($sku)){
                return false;
            }
            $array_product_id = array();
            array_push($array_product_id,$product_id);
            $newProduct = get_post_meta($product_id);
            if(!empty($newProduct)
                && isset($newProduct['_old_product_id'])
                && isset($newProduct['_old_product_id'][0])){
                array_push($array_product_id,$newProduct['_old_product_id'][0]);
            }
            $reject_id = '';

            if(!empty($array_product_id)){
                $list_id = join(',',$array_product_id);
                $list_id = str_replace(',','',$list_id);
                $reject_id = "AND pm.post_id not in(" .$list_id. ")";
            }
            $post_table = $wpdb->get_results( sprintf("SELECT pm.* FROM $wpdb->posts p, $wpdb->postmeta pm
                                              WHERE p.`ID` = pm.`post_id` AND p.`post_type` in ('product','temp','product_variation')
                                              AND pm.`meta_key` = '_sku' AND pm.`meta_value` = '%s' %s", $sku, $reject_id), ARRAY_A);
            if(!empty($post_table)){
                return $this->response_handler(false, array('message' => __('SKU already exists.', 'fnt'), 'old_sku' => $old_sku));
            }
            else{
                return $this->response_handler(true, array('message' => __('SKU not exists.', 'fnt'), 'old_sku' => $old_sku));
            }
        }catch (Exception $ex){
            return $this->response_handler(false,array('message' => $ex->getMessage(), 'old_sku' => $old_sku));
        }
    }
    private function delete_products($posted_data = array()) {
        $product_ids = isset($posted_data['allProductData'])?$posted_data['allProductData']:array();
        $result = WC_Product_Custom::delete_permanently_multiple_products($product_ids);
        return $this->response_handler($result, array('message' => 'doing delete'));
    }
    private function move_products_to_trash($posted_data = array()) {
        $product_ids = isset($posted_data['allProductData'])?$posted_data['allProductData']:array();
        $result = WC_Product_Custom::move_to_trash_multiple_products($product_ids);
        return $this->response_handler($result, array('message' => 'doing move to trash'));
    }
    private function change_product_type() {
        $posted_data = $_POST;
        $product_ids = isset( $posted_data['allProductData'] ) ? $posted_data['allProductData'] : array();
        $product_type = isset( $posted_data['productType'] ) ? $posted_data['productType'] : -1;
        if ( $product_type == -1 ) {
            return $this->response_handler( false, array('message' => 'Lost data when change product type!') );
        }
        $result = WC_Product_Custom::change_products_type( $product_ids, $product_type );
        return $this->response_handler( $result, array('message' => 'doing change product type') );
    }
    private function restore_products($posted_data = array()) {
        $product_ids = isset($posted_data['allProductData'])?$posted_data['allProductData']:array();
        $result = WC_Product_Custom::restore_multiple_products($product_ids);
        return $this->response_handler($result, array('message' => 'doing move to trash'));
    }
    private function generate_row($posted_data = array()) {
        $result = '';
        $product = isset($posted_data['data']) ? $posted_data['data'] : array();
        if(!empty($product)){
        }
        return $result;
    }

    private function backform_check_sku($sku= ''){
        if(empty($sku)){
            return false;
        }
        global $wpdb;
        $post_sku = $wpdb->get_results( $wpdb->prepare("SELECT pm.* FROM  wp_posts p, wp_postmeta pm
                                        WHERE p.`ID` = pm.`post_id` AND p.`post_type` in ('product','temp')
                                        AND pm.`meta_key` = '_sku' AND pm.`meta_value` = '%s' ", $sku), ARRAY_A);
        if(!empty($post_sku)){
            return true;
        }
        else{
            return false;
        }
    }

    public function action_controller_callback() {
        $json_response = null;
        if (isset($_POST['real_action']) || isset($_GET['real_action'])) {
            if ($_POST['real_action']) {
                $action = $_POST['real_action'];
            } elseif ($_GET['real_action']) {
                $action = $_GET['real_action'];
            } else {
                $action = '';
            }
            $error_array = array();
            switch ($action) {
                case 'add_multiple':
                    if(isset($_POST['all_product_data'])){
                        $products_add_data = $_POST['all_product_data'];
                    }else{
                        $products_add_data = array();
                    }
                    WC_Product_Custom::add_products($products_add_data,$error_array);
                    $json_response = $this->response_handler(true,json_encode($error_array));
                    break;
                case 'edit_multiple': // when edit inline
                    // check all data was passed
                    $data_passed = isset( $_POST['all_product_data'] ) && isset( $_POST['all_product_content'] );
                    if ( $data_passed ) { // if passed
                        $all_product_data = Fnt_Core::extractPassedData($_POST['all_product_data']);
                        // if extract data of product list is not empty and is an array
                        if ( ! empty( $all_product_data ) && is_array( $all_product_data ) ) {
                            // if extra data is not empty and is an array
                            if ( ! empty( $_POST['all_product_content'] ) && is_array( $_POST['all_product_content'] ) ) {
                                // merge product content, excerpt
                                // and variation_description since ver 1.1
                                $all_product_content = $_POST['all_product_content'];
                                foreach($all_product_data as $key => $value) {
                                    foreach($all_product_content[$key] as $sub_key => $sub_value) {
                                        $value[$sub_key] = $sub_value;
                                    }
                                    // update value of item
                                    $all_product_data[$key] = $value;
                                }
                            }
                            // delete product content, excerpt data
                            // and variation_description, variable attributes since ver 1.1
                            $all_product_content = null;

                            $this->multiple_edit_inline($all_product_data, $error_array);
                            if ( empty( $error_array ) ) {
                                if ( ! isset( $_POST['save_variation'] ) ) {
                                    if($_POST['save_all'] == "1"){
                                        update_option('fnt-save-all-data-successfully', 1);
                                    }else{
                                        update_option('fnt-save-all-data-successfully', 2);
                                    }
                                }
                                $json_response = $this->response_handler(true,array('error_array' => '', 'save_all' => $_POST['save_all']));
                            } else {
                                $json_response = $this->response_handler(false,array('error_array' => $error_array,'save_all' => $_POST['save_all']));
                            }
                        } else {
                            $data = array('error_array' => array(__('Lost data when edit inline!', 'fnt')),'save_all' => 'failed');
                            $json_response = $this->response_handler(false, $data);
                        }
                    } else { // if lost data
                        $data = array('error_array' => array( __( 'Lost data when edit inline!', 'fnt' )),'save_all' => 'failed');
                        $json_response = $this->response_handler(false, $data);
                    }
                    break;
                case 'save_all_product_data':
                    $json_response = $this->response_handler(true, array('message' => 'Request is not available'));
                    break;
                case 'product_tag':
                    $json_response = $this->autocomplete_product_tag($_POST);
                    break;
                case 'check_sku':
                    $json_response = $this->check_sku_not_exists($_POST);
                    break;
                case 'delete_product':
                    $json_response = $this->delete_products($_POST);
                    break;
                case 'move_product_to_trash':
                    $json_response = $this->move_products_to_trash($_POST);
                    break;
                case 'change_product_type':
                    $json_response = $this->change_product_type();
                    break;
                case 'restore_product':
                    $json_response = $this->restore_products($_POST);
                    break;
                case 'generate_row':
                    $json_response = $this->generate_row($_POST);
                    break;
                case 'save_settings':
                    $json_response = $this->save_settings_data_handler($_POST);
                    break;
                case 'check_sku_backform':
                    if(isset($_POST['new_sku'])){
                        $new_sku = $_POST['new_sku'];
                    }else{
                        $new_sku = '';
                    }
                    if(isset($_POST['form_id'])){
                        $form_id = $_POST['form_id'];
                    }else{
                        $form_id = '';
                    }
                    $resultCheckSku = $this->backform_check_sku($new_sku);
                    $json_response = $this->response_handler($resultCheckSku, array('form_id' => $form_id));
                    break;
                // this case will get product via ProductID
                case 'get_product_just_edit':
                    // check data pass
                    if ( isset( $_POST['product_id'] ) ) {
                        // get product_id form ajax
                        $product_id = $_POST['product_id'];
                        // get all fields of product need to replace data in row for not reload page
                        $response = Fnt_CustomProductList::get_product_fields_by_product_id($product_id);
                        // Response
                        $result = $response['result'];
                        if($result) {
                            $response['data']['cat_selection'] = WC_Product_Custom::get_cat_selection();
                            $data = $response['data'];
                        } else {
                            $data = $response['error-message'];
                        }
                        $json_response = $this->response_handler($result, $data);
                    } else {
                        $data = __( 'Lost data when get product just modify!', 'fnt' );
                        $json_response = $this->response_handler(false, $data);
                    }
                    break;
                case 'call_function_edit_product':
                    // check all data was send
                    if ( isset( $_POST['edit_mode'] ) && isset( $_POST['content'] ) && isset( $_POST['excerpt'] ) && isset( $_POST['product_data'] ) ) {
                        // Get mode
                        $edit_mode = $_POST['edit_mode'];
                        // get content and excerpt
                        $product_content = $_POST['content'];
                        $product_excerpt = $_POST['excerpt'];
                        // Extract data from ajax and push content and excerpt to this array
                        $product_data = Fnt_Core::extractPassedData($_POST['product_data']);
                        // if extract success
                        if ( ! empty( $product_data ) && is_array( $product_data ) ) {
                            $product_data['content'] = $product_content;
                            $product_data['excerpt'] = $product_excerpt;

                            // Call function to edit post
                            $response = $this->call_function_edit_post( $product_data, $edit_mode );
                            $result = $response['result'];
                            if ( $result ) {
                                $data = $response['data'];

                            } else {
                                $data = $response['error-message'];
                            }
                            // Response
                            $json_response = $this->response_handler($result, $data);
                        } else {
                            $data = __( 'Lost data when edit product!', 'fnt' );
                            $json_response = $this->response_handler(false, $data);
                        }
                    } else {
                        $data = __( 'Lost data when edit product!', 'fnt' );
                        $json_response = $this->response_handler(false, $data);
                    }
                    break;
                case 'get_product_variations_just_edit':
                    $data_table = Fnt_CustomProductList::ajax_render_variations_area_data();
                    $attributes_data = Fnt_CustomProductList::ajax_render_attributes_area_data();
                    $data_table = array_merge( $data_table, $attributes_data );
                    if ( ! $data_table ) {
                        $data = __( 'Lost data when edit product!', 'fnt' );
                        $json_response = $this->response_handler(false, $data);
                    } else {
                        $json_response = $this->response_handler(true, $data_table);
                    }
                    break;
                case 'delete_product_variations':
                    // Call function to delete selected product
                    // After that, if success: call function to render product variations of ParentID
                    // if failed: return message of error
                    $product_ids = isset( $_POST['selectedProductID'] ) ? $_POST['selectedProductID'] : array();
                    $result = WC_Product_Variation_Custom::delete_permanently_multiple_products( $product_ids );

                    // if delete product variation success, call function to render table variations after delete
                    if ( $result ) {
                        $data_table = Fnt_CustomProductList::ajax_render_variations_area_data();
                        if ( ! $data_table ) {
                            $data = __( 'Lost data when edit product!', 'fnt' );
                            $json_response = $this->response_handler(false, $data);
                        } else {
                            $json_response = $this->response_handler(true, $data_table);
                        }
                    } else {
                        $json_response = $this->response_handler( $result, __( 'Delete product variation failed!', 'fnt' ) );
                    }
                    break;
                case 'save_attributes':
                    // save attributes in popup
                    $product_attributes = isset( $_POST['product_attributes'] ) ? $_POST['product_attributes'] : array();
                    $product_id = isset( $_POST['product_id'] ) ? $_POST['product_id'] : 0;
                    $product_type = isset( $_POST['product_type'] ) ? $_POST['product_type'] : '';
                    $result = WC_Product_Custom::update_attributes( $product_id, $product_attributes );

                    // if save attributes success, call function to render table variations after save
                    if ( $result ) {
                        // If is product simple, not need update variation products data
                        if ( $product_type == 'simple' ) {
                            $json_response = $this->response_handler(true, __( 'Save simple attributes success', 'fnt' ) );
                        } else {
                            $data_table = Fnt_CustomProductList::ajax_render_variations_area_data();
                            if ( ! $data_table ) {
                                $data = __( 'Lost data when save product attributes!', 'fnt' );
                                $json_response = $this->response_handler(false, $data);
                            } else {
                                $json_response = $this->response_handler(true, $data_table);
                            }
                        }
                    } else {
                        $json_response = $this->response_handler( $result, __( 'Save product attributes failed!', 'fnt' ) );
                    }
                    break;
                case 'add_attributes':
                    // add a blank attribute
                    if ( isset( $_POST['screen_id'] ) ) {
                        $screen_id = $_POST['screen_id'];

                        $screen = convert_to_screen( $screen_id );
                        $args = array( 'screen' => $screen );

                        $attributes_table = new Fnt_ProductAttributes( $args );
                        $html_data = $attributes_table->render_blank_attribute_row();
                        if ( ! $html_data ) {
                            $json_response = $this->response_handler( false, __( 'Add attributes failed!', 'fnt' ) );
                        } else {
                            $json_response = $this->response_handler( true, $html_data );
                        }
                    } else {
                        $json_response = $this->response_handler( false, __( 'Add attributes failed!', 'fnt' ) );
                    }

                    break;

                case 'add_attribute_term':
                    // add new attribute term
                    if ( isset( $_POST['taxonomy'] ) && isset( $_POST['term'] ) ) {
                        $result = self::add_new_attribute_term();
                        if ( ! $result['result'] ) {
                            $json_response = $this->response_handler( false, $result['message'] );
                        } else {
                            $json_response = $this->response_handler( true, $result['message'] );
                        }
                    } else {
                        $json_response = $this->response_handler( false, __( 'Lost data when add attribute item!', 'fnt' ) );
                    }

                    break;
                case 'save_default_attributes':
                    // save default attribute
                    if ( isset( $_POST['selectedAttributes'] ) && isset( $_POST['productID'] ) ) {
                        $product_id = $_POST['productID'];
                        $default_attributes = $_POST['selectedAttributes'];
                        $result = WC_Product_Custom::saveDefaultAttributes( $product_id, $default_attributes );
                        if ( ! $result['result'] ) {
                            $json_response = $this->response_handler( false, $result['message'] );
                        } else {
                            $json_response = $this->response_handler( true, $result['message'] );
                        }
                    } else {
                        $json_response = $this->response_handler( false, __( 'Lost data when set default product attributes!', 'fnt' ) );
                    }

                    break;
                case 'add_blank_variation_product':
                    if ( isset( $_POST['product_id'] ) && isset( $_POST['screen_id'] ) ) {
                        $product_id = $_POST['product_id'];
                        $result = WC_Product_Variation_Custom::add_blank_variation( $product_id );

                        // if save attributes success, call function to render table variations after save
                        if ( $result ) {
                            $data_table = Fnt_CustomProductList::ajax_render_variations_area_data();
                            if ( ! $data_table ) {
                                $data = __( 'Lost data when add product variation!', 'fnt' );
                                $json_response = $this->response_handler(false, $data);
                            } else {
                                $json_response = $this->response_handler(true, $data_table);
                            }
                        } else {
                            $json_response = $this->response_handler( $result, __( 'Add variation product failed!', 'fnt' ) );
                        }
                    } else {
                        $json_response = $this->response_handler( false, __( 'Lost data when add variation product!', 'fnt' ) );
                    }
                    break;
                /**
                 * Get new variable price sync
                 */
                case 'get_variable_price_change':
                    if ( isset( $_POST['currentVariableID'] ) ) {
                        $current_variable_id = $_POST['currentVariableID'];
                        $result = WC_Product_Variable_Custom::get_ajax_price_sync( $current_variable_id );
                        $json_response = $this->response_handler( true, $result );
                    } else {
                        $json_response = $this->response_handler( false, __( 'Lost data when add get variable price change!', 'fnt' ) );
                    }
                    break;
                default:
                    $json_response = $this->response_handler(false, array('message' => __( 'Request is not available', 'fnt' )));
                    break;
            }
        } else {
            $json_response = $this->response_handler(false, array('message' => __( 'Request is not available', 'fnt' )));
        }
        echo $json_response;
        exit;
    }

    /*
     * Function use to edit post via POST variable in form
     * Return PostID if success
     */
    private function call_function_edit_post( $product_data, $edit_mode ) {
        try {
            if ( empty( $product_data ) || ! is_array( $product_data ) || empty( $edit_mode ) ) {
                return array(
                    'result' => false,
                    'error-message' => 'Product data null!'
                );
            }
            // fake data pass to POST
            $_POST = $product_data;
            // Call default function of wordpress
            $postID = edit_post();
            if ( $postID < 1 ) {
                return array(
                    'result' => false,
                    'error-message' => 'Edit failed!'
                );
            }
            // if is add new product, add flag to painting row when reload
            if ( $edit_mode == 'add' ) {
                // update post meta to
                update_post_meta( $postID, '_just_add', '1' );
                // update product_status
                // ThienLD : get product status which is configured in setting popup and set to a created product
                $posts['post_status'] = Fnt_Core::get_plugin_setting_value_by_key('setProductStatusOnCreating','pending');
                $posts['ID'] = $postID;
                $postID = wp_update_post( $posts );

                // return error if update post failed
                if ( is_wp_error( $postID ) ) {
                    return array(
                        'result' => false,
                        // get error from wp_error
                        'error-message' => $postID->get_error_message()
                    );
                }
            }

            return array (
                'result' => true,
                'data' => $postID
            );
        } catch ( Exception $ex ) {
            return array(
                'result' => false,
                'error-message' => $ex->getMessage()
            );
        }
    }

    /**
     * Add a new attribute item via ajax function
     */
    public static function add_new_attribute_term() {
        if ( ! current_user_can( 'manage_product_terms' ) ) {
            return array(
                'result'  => false,
                'message' => __( 'User can not do this action.', 'fnt' )
            );
        }

        $taxonomy = esc_attr( $_POST['taxonomy'] );
        $term     = wc_clean( $_POST['term'] );

        if ( taxonomy_exists( $taxonomy ) ) {

            $result = wp_insert_term( $term, $taxonomy );

            if ( is_wp_error( $result ) ) {
                return array(
                    'result'  => false,
                    'message' => $result->get_error_message()
                );
            } else {
                $term = get_term_by( 'id', $result['term_id'], $taxonomy );
                return array(
                    'result'  => true,
                    'message' => array(
                        'term_id'  => $term->term_id,
                        'name'     => $term->name,
                        'slug'     => $term->slug,
                        'taxonomy' => $taxonomy
                    )
                );
            }
        } else {
            return array(
                'result'  => true,
                'message' => __('Taxonomy does not exists.')
            );
        }
    }
}