<?php
class WC_Product_Custom extends WC_Product {
    /**
     * Initialize simple product.
     *
     * @param mixed $product
     */
    public function __construct( $product ) {
        parent::__construct( $product );
    }
    /*
     * NOTICE: This is just support for default attributes, not support for custom attributes
     * This function will add default field for each attribute value
     * For insert to database
     * Before
     * '_product_attributes' =>
     *    array (
     *        0 => 'color-a',
     *    )
     * After
     * '_product_attributes' =>
     *    array (
     *        'pa_color-a' =>
     *          array (
     *            'name' => 'pa_color-a',
     *            'value' => '',
     *            'position' => '0',
     *            'is_visible' => 1,
     *            'is_variation' => 1,
     *            'is_taxonomy' => 1,
     *          )
     * )
     */
    public static function format_attributes_before_insert( $attributes ) {
        $attributes_formatted = array();
        $i= 0;
        foreach ( $attributes as $attribute ) {
            $attribute_formatted = array(
                'name'         => wc_attribute_taxonomy_name( $attribute ),
                'value'        => '',
                'position'     => $i,
                'is_visible'   => 1,
                'is_variation' => 1,
                'is_taxonomy'  => 1,
            );
            $attributes_formatted[ wc_attribute_taxonomy_name($attribute) ] = $attribute_formatted;
            $i++;
        }
        return $attributes_formatted;
    }
    /*
     * This function use to format product field
     * Group columns have same table name in database
     */
    public static function format_product_before_insert( $product ) {
        $product_formatted = array();
        // Get value attributes
        $product_attributes = Fnt_Core::get_product_attributes();
        foreach ( $product as $key => $value ) {
            // $key have table name
            if ( isset( Fnt_ProductListCons::$column_table_in_db[ $key ] ) ) {
                $table_in_db = Fnt_ProductListCons::$column_table_in_db[ $key ];
                if( isset( Fnt_ProductListCons::$column_mapping[ $key ] ) ) {
                    foreach ( Fnt_ProductListCons::$column_mapping[ $key ] as $mapping_key => $mapping_value ) {
                        if( $mapping_value == $value ) {
                            $value = $mapping_key;
                        }
                    }
                }
                if( $key == Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_GALLERY] && is_array($value)){
                    $value = implode(',',$value);
                }
                $product_formatted[ $table_in_db ][ Fnt_ProductListCons::$column_name_in_db[ $key ] ] = $value;
            } else { // don't have table name
                // get product attributes
                if( array_key_exists( $key, $product_attributes ) ) {
                    if ( WC_Product_Variation_Custom::get_attribute_slug_by_name(wc_attribute_taxonomy_name($key), $value) == null) {
                        $product_formatted['attribute_value'][$key] = $value;
                    } else {
                        $product_formatted['attribute_value'][$key] = WC_Product_Variation_Custom::get_attribute_slug_by_name(wc_attribute_taxonomy_name($key), $value);
                    }
                }
            }
        }
        /*
         * Since ver 1.1
         * unset if $product_formatted have "product_type" is "variation"
         * Because Woocommerce is not have product type "variation"
        */
        if ( isset( $product_formatted['terms']['product_type'] ) && $product_formatted['terms']['product_type'] == 'variation' ) {
            unset( $product_formatted['terms']['product_type'] );
        }
        /* End since ver 1.1 */
        return $product_formatted;
    }
    /* Add new multiple products */
    public static function add_products( $products = array(), &$error_array = array() ) {
        // If list product don't have any items
        if( empty( $products ) ) {
            $error_array = 'Input is empty!';
            return;
        }
        $error = '';
        // browse through all product
        foreach ( $products as $product ) {
            // if product empty or not is an array, ignore
            if ( empty( $product ) || ! is_array( $product ) ) {
                $error .= __('Product item have incorrect format','fnt');
                continue;
            }
            // If validation for product is OK, add product
            // Check product type
            if ( empty( $product['product_type'] ) ) {
                $error .= __('Product item don"t have product type','fnt');
                continue;
            } else {
                if( $product['product_type'] == 'variable' ) {
                    self::save_product_variable( $product );
                } else {
                    self::add_product( $product, $error );
                }
            }
            if ( ! empty( $error ) ) {
                array_push( $error_array, $error );
            }
        }
    }
    // Function use to save product variable with product variation
    private static function save_product_variable( $product ) {
        // Check input data
        // Check validation for $product, it must be an array
        if( ! is_array( $product ) ) {
            return; // ignore this item
        }
        // At first, save product variable
        WC_Product_Custom::add_product( $product );
        // Next, browse and save all product variation of this product
        // Get product variation
        $product_variations = $product['variation_items'];
        foreach ( $product_variations as $product_variation ) {
            // Save this product
            WC_Product_Variation_Custom::add_product( $product_variation );
        }
    }
    /*
     * Add new single product
     * return product_id just add
     */
    public static function add_product($product = array(), &$error='') {
        try{
            if(empty($product)) {
                $error = __('Product empty', 'fnt' );
                return Fnt_ProductAddNewItemInModel::product_empty;
            } else {
                $product = self::format_product_before_insert( $product );
                $posts = array();
                $post_meta = array();
                $terms = array();
                $product_attributes = array();
                if ( isset( $product['posts'] ) && is_array( $product['posts'] ) ) {
                    $posts = $product['posts'];
                    $posts['ID'] = null;
                    // extra fields default
                    $posts['post_status'] = 'pending';
                    $posts['post_type'] = 'product';

                    if ( isset( $product['postmeta'] ) && is_array( $product['postmeta'] ) ) {
                        $post_meta = $product['postmeta'];
                        $post_meta['_just_add'] = '1';
                        $product_attributes = isset($post_meta['_product_attributes'])?$post_meta['_product_attributes']:array();
                        $post_meta['_product_attributes'] = self::format_attributes_before_insert($product_attributes);
                    }
                    if ( isset( $product['terms'] ) && is_array( $product['terms'] ) ) {
                        $terms = $product['terms'];
                        // if have attribute, add all value of this to terms table in db
                        foreach($product_attributes as $product_attribute) {
                            // get all options of attribute
                            $child_attributes = get_terms(wc_attribute_taxonomy_name($product_attribute), array('hide_empty' => 0));
                            $term = array();
                            foreach($child_attributes as $child_attribute) {
                                $term[] = $child_attribute->slug;
                            }
                            $terms[wc_attribute_taxonomy_name($product_attribute)] = $term;
                        }
                    }

                    // Create post
                    $post_id = wp_insert_post( $posts );
                    if(!is_numeric($post_id)) {
                        $error = __("can't inset new product", 'fnt' );
                        return Fnt_ProductAddNewItemInModel::product_insert_failed;
                    } else {
                        // update terms
                        foreach (  $terms as $key => $value ) {
                            if ( is_array( $value ) ) {
                                // if it is categories format value of array to integer
                                if ( $key == 'product_cat' ) {
                                    $value = array_map( 'intval', $value );
                                    $value = array_unique( $value );
                                }
                                $array_terms = array();
                                foreach ( $value as $term ) {
                                    $array_terms[] = $term;
                                }
                                wp_set_object_terms( $post_id, $array_terms, $key );
                            } else {
                                wp_set_object_terms( $post_id, $value, $key );
                            }
                        }
                        // update post meta
                        foreach ( $post_meta as $key => $value ) {
                            update_post_meta( $post_id, $key, $value );
                        }
                    }
                } else {
                    $error = __("don't have product data to add", 'fnt' );
                    return Fnt_ProductAddNewItemInModel::product_not_exists;
                }
            }
        } catch (Exception $ex){
            $error = __('Add product error: ' . $ex->getMessage(), 'fnt' );
            return Fnt_ProductAddNewItemInModel::exception;
        }
        return $post_id;
    }

    /*
     * Update multiple exists products
     */
    public static function update_products( $product_list = array(), &$error_array = array() ) {
        $error_array = array();
        if ( ! empty( $product_list) ) {
            foreach ( $product_list as $product ) {
                if ( empty( $product ) ) {
                    continue;
                }
                if ( ! isset( $product['modifying_product'] ) || $product['modifying_product'] != '1' ) {
                    continue;
                }
                $error ='';
                $result = self::update_product( $product, $error );
                if ( ! empty( $error ) ) {
                    array_push( $error_array, $error );
                }
            }
        }
    }
    /*
     * Update single exists product
     */
    public static function update_product($product, &$error='') {
        try{
            if(empty($product)) {
                $error = __('product empty', 'fnt' );
                return Fnt_ProductAddNewItemInModel::product_empty;
            } else {
                // since ver 1.1, use for know parent product if current product is "variation"
                $parent_product_id = 0;
                if ( isset( $product['parent_product_id'] ) ) {
                    $parent_product_id = $product['parent_product_id'];
                }
                // end code update for ver 1.1
                $product_type = isset( $product['product_type'] ) ? $product['product_type'] : '';
                $product = self::format_product_before_insert( $product );
                $posts = array();
                $post_meta = array();
                $terms = array();
                if ( isset( $product['posts'] ) && is_array( $product['posts'] ) ) {
                    $posts = $product['posts'];
                    if ( isset( $product['postmeta'] ) && is_array( $product['postmeta'] ) ) {
                        $post_meta = $product['postmeta'];

                        if ( isset( $post_meta['_product_attributes'] ) && is_array( $post_meta['_product_attributes'] ) ) {
                            foreach( $post_meta['_product_attributes'] as $key => $value ) {
                                $post_meta[$key] = $value;
                            }

                            unset( $post_meta['_product_attributes'] );
                        }

                        // format stock quantity to number
                        if ( isset( $post_meta['_stock'] ) && is_numeric( $post_meta['_stock'] ) ) {
                            $stock_quantity = intval($post_meta['_stock']);
                            // save stock quantity as integer number
                            $post_meta['_stock'] = $stock_quantity;
                        }
                    }
                    if ( isset( $product['terms'] ) && is_array( $product['terms'] ) ) {
                        $terms = $product['terms'];
                    }

                    // Update post
                    $post_id = wp_update_post( $posts );
                    if(!is_numeric($post_id)) {
                        $error = __("can't insert new product", 'fnt' );
                        return Fnt_ProductAddNewItemInModel::product_insert_failed;
                    } else {
                        // update terms
                        foreach ( $terms as $key => $value ) {
                            if ( is_array( $value ) ) {
                                // if it is categories format value of array to integer
                                if ( $key == 'product_cat' ) {
                                    $value = array_map( 'intval', $value );
                                    $value = array_unique( $value );
                                }
                                $array_terms = array();
                                foreach ( $value as $term ) {
                                    $array_terms[] = $term;
                                }
                                wp_set_object_terms( $post_id, $array_terms, $key );
                            } else {
                                wp_set_object_terms( $post_id, $value, $key );
                            }
                        }

                        // update postmeta
                        foreach ( $post_meta as $key => $value) {
                            // Fix issue compatibility with plugin currency switcher
                            if ( ! empty ( $terms['product_type'] ) && $terms['product_type'] == 'variable'
                            && ( $key == '_regular_price' || $key == '_sale_price' ) ) {
                                continue;
                            }
                            update_post_meta( $post_id, $key, $value );
                        }

                        // @fixbug: can't set price, in front end show free
                        // update product price, base regular price and sale price
                        if ( ! empty ( $product_type ) && $product_type != 'variable' ) {
                            $price = '';
                            if ( isset( $post_meta['_sale_price'] ) && ! empty( $post_meta['_sale_price'] ) ) {
                                $price = $post_meta['_sale_price'];
                            } else if ( isset( $post_meta['_regular_price'] ) && ! empty( $post_meta['_regular_price'] ) ) {
                                $price = $post_meta['_regular_price'];
                            }
                            update_post_meta( $post_id, '_price', $price );
                        }
                        // end update price

                        // since ver 1.1 -------
                        // check if product is variation and have parent product, sync variation with itself variable
                        if ( $parent_product_id != 0 ) {
                            // make manage stock change of variation product correct with default flow of WC
                            if ( isset( $post_meta['_manage_stock'] ) ) {
                                // if uncheck manage stock of variation, remove 2 fields of postmeta
                                if ( $post_meta['_manage_stock'] == 'no' ) {
                                    delete_post_meta( $post_id, '_stock' );
                                    delete_post_meta( $post_id, '_backorders' );
                                }
                                // if check manage stock of variation, add 2 fields with default value
                                if ( $post_meta['_manage_stock'] == 'yes' ) {
                                    update_post_meta( $post_id, '_stock', isset( $post_meta['_stock'] ) ? $post_meta['_stock'] : 0 );
                                    update_post_meta( $post_id, '_backorders', isset( $post_meta['_backorders'] ) ? $post_meta['_backorders'] : 'no' );
                                }
                            }

                            WC_Product_Variable::sync( $parent_product_id );
                            wc_delete_product_transients( $parent_product_id );
                        }
                        // use to make stock status correctly
                        if ( isset ( $post_meta['_stock_status'] ) ) {
                            update_post_meta( $post_id, '_stock_status', $post_meta['_stock_status'] );
                        }
                        //---------------------
                        /**
                         * Sync product meta data in some special cases since woocommerce 3.x
                         *
                        */
                        $_pf = new WC_Product_Factory_Custom();
                        $currentProduct = $_pf->get_product( $post_id );
                        $currentProduct->set_woocommerce_general_properties();
                    }

                } else {
                    $error = __("Don't have product data to add", 'fnt' );
                    return Fnt_ProductAddNewItemInModel::product_not_exists;
                }
            }
        } catch (Exception $ex){
            $error = __('Add product error: ' . $ex->getMessage(), 'fnt' );
            return Fnt_ProductAddNewItemInModel::exception;
        }
    }
    /*
     * Delete multiple products
     */
    public static function delete_permanently_multiple_products( $list_id = array() ) {
        if ( empty( $list_id ) ) {
            return false;
        } else {
            foreach ( $list_id as $id ) {
                WC_Product_Custom::delete_permanently_product( $id );
            }
            return true;
        }
    }
    /*
     * Delete single product
     */
    public static function delete_permanently_product( $product_id = 0, &$error='' ) {
        try {
            if ( $product_id === 0 ) {
                return Fnt_ProductAddNewItemInModel::product_not_exists;
            } else {
                $product = get_post( $product_id, 'ARRAY_A' );
                if ( ! empty( $product ) ) {
                    $result = wp_delete_post( $product_id, true );
                    if ( $result !== null && !empty($result) ) {
                        return $product_id;
                    } else {
                        return Fnt_ProductAddNewItemInModel::product_un_error;
                    }
                } else {
                    return Fnt_ProductAddNewItemInModel::product_not_exists;
                }
            }
        } catch ( Exception $ex ) {
            $error = $ex->getMessage();
            return Fnt_ProductAddNewItemInModel::exception;
        }
    }
    public static function delete_term_relationships( $product_id ) {
        global $wpdb;
        $sql = "DELETE FROM {$wpdb->term_relationships} WHERE `object_id` = $product_id";
        return $wpdb->query( $sql );
    }
    /*
     * Move multiple products to trash
     */
    public static function move_to_trash_multiple_products( $list_id = array() ) {
        if ( empty( $list_id ) ) {
            return false;
        } else {
            foreach ( $list_id as $id ) {
                self::move_to_trash( $id );
            }
            return true;
        }
    }
    /*
     * Move single product to trash
     */
    public static function move_to_trash( $product_id = 0, &$error='' ) {
        try {
            if ( $product_id === 0 ) {
                return Fnt_ProductAddNewItemInModel::product_not_exists;
            } else {
                $product = get_post( $product_id, 'ARRAY_A' );
                if ( ! empty( $product ) ) {
                    //move to trash
                    $result = wp_trash_post( $product_id );
                    if ( $result !== null && !empty($result) ) {
                        return $product_id;
                    } else {
                        return Fnt_ProductAddNewItemInModel::product_un_error;
                    }
                }else{
                    return Fnt_ProductAddNewItemInModel::product_not_exists;
                }
            }
        } catch ( Exception $ex ) {
            $error= $ex->getMessage();
            return Fnt_ProductAddNewItemInModel::exception;
        }
    }
    /*
     * Change multiple products type
     */
    public static function change_products_type( $list_id = array(), $product_type = 0 ) {
        if ( empty( $list_id ) || $product_type == 0 ) {
            return false;
        } else {
            foreach ( $list_id as $id ) {
                self::change_product_type( $id, $product_type );
            }
            return true;
        }
    }
    /*
     * Change single product type
     */
    public static function change_product_type( $product_id = 0, $product_type, &$error='' ) {
        try {
            if ( $product_id === 0 ) {
                return Fnt_ProductAddNewItemInModel::product_not_exists;
            } else {
                $product = get_post( $product_id, 'ARRAY_A' );
                if ( ! empty( $product ) ) {
                    // convert to real integer
                    $product_type = intval( $product_type );
                    // change product type
                    $result = wp_set_object_terms( $product_id, $product_type, 'product_type' );
                    if ( is_wp_error( $result ) ) {
                        return Fnt_ProductAddNewItemInModel::product_un_error;
                    } else {
                        return $result;
                    }
                }else{
                    return Fnt_ProductAddNewItemInModel::product_not_exists;
                }
            }
        } catch ( Exception $ex ) {
            $error= $ex->getMessage();
            return Fnt_ProductAddNewItemInModel::exception;
        }
    }
    /*
     * Restore single product from trash
     */
    public static function restore_product( $product_id = 0, &$error='' ) {
        try {
            if ( $product_id === 0 ) {
                return Fnt_ProductAddNewItemInModel::product_not_exists;
            } else {
                $product = get_post( $product_id, 'ARRAY_A' );
                if ( ! empty( $product ) ) {
                    $result = wp_untrash_post( $product_id );
                    if ( $result !== null && !empty($result) ) {
                        return $product_id;
                    } else {
                        return Fnt_ProductAddNewItemInModel::product_un_error;
                    }
                } else {
                    return Fnt_ProductAddNewItemInModel::product_not_exists;
                }
            }
        } catch ( Exception $ex ) {
            $error= $ex->getMessage();
            return Fnt_ProductAddNewItemInModel::exception;
        }
    }
    /*
     * Restore multiple products from trash
     */
    public static function restore_multiple_products( $list_id = array() ) {
        if ( empty($list_id) ) {
            return false;
        } else {
            foreach ( $list_id as $id ) {
                self::restore_product( $id );
            }
            return true;
        }
    }
    /*
     * Get product fields as array
     */
    public function get_list_product_fields( $columns ) {
        $product_fields = array();
        foreach ( $columns as $column => $value ) {
            if ( isset( Fnt_ProductListCons::$column_name_in_db[ $column ] ) ) {
                $column_name_in_db = Fnt_ProductListCons::$column_name_in_db[ $column ];
                $column_name_in_object = trim( $column_name_in_db, '_' );
                switch ( $column ) {
                    case Fnt_ProductListCons::COLUMN_TAG:
                        $product_fields[ $column_name_in_db ] = $this->get_product_list_term_meta( $column_name_in_object, 'name', 'array' );
                        break;
                    case Fnt_ProductListCons::COLUMN_CATEGORIES:
                        $product_fields[ $column_name_in_db ] = $this->get_product_list_term_meta( $column_name_in_object, 'term_id', 'array' );
                        break;
                    // since ver 1.1, to get product attribute for product variation
                    case Fnt_ProductListCons::COLUMN_ATTRIBUTE:
                        $product_fields[ $column_name_in_db ] = $this->get_variation_attributes();
                        break;
                    // end since ver 1.1
                    default:
                        if ( Fnt_ProductListCons::$column_table_in_db[ $column ] == 'posts' ) {
                            $product_fields[ $column_name_in_db ] = $this->get_post_data()->$column_name_in_object;
                        } else {
                            $product_fields[ $column_name_in_db ] = $this->$column_name_in_object;
                        }
                        break;
                }
            }
        }
        if ( ! empty( $this->old_product_id ) ) {
            $product_fields['old_product_id'] = $this->old_product_id;
        }
        // since ver 1.1
        // use for get parent product id if current product type is variation
        if ( $this->product_type == 'variation' ) {
            $product_fields['parent_product_id'] = $this->get_post_data()->post_parent;
        }
        return $product_fields;
    }
    /*
     * Get gallery images of product
     */
    public function get_image_gallery() {
        $attachment_ids = $this->get_gallery_image_ids();
        $image_link = '';
        // if $attachment_ids is not array
        if ( ! is_array( $attachment_ids ) ) {
            return '<span class="wrap-gallery-image wrapper-gallery-plus">
                                  <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                            </span>';
        }
        foreach ( $attachment_ids as $attachment_id ) {
            $featured_image = wp_get_attachment_image_src( $attachment_id );
            $image_link .= "<span class='wrap-gallery-image'>
                                <span class='glyphicon glyphicon-remove-circle remove-gallery-image' data-attachment-id='$attachment_id'></span>
                                <img src='$featured_image[0]' width='40px' height='40px' class='image-thumb-gallery'/>
                            </span>";
        }

        $image_link .= '<span class="wrap-gallery-image wrapper-gallery-plus">
                                  <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                            </span>';
        return $image_link;
    }
    /*
     * Get thumbnail image of product
     */
    public function get_image_thumbnail( $size = 'shop_thumbnail', $attr = array() ) {
        if ( has_post_thumbnail( $this->id ) ) {
            $image = get_the_post_thumbnail( $this->id, $size, $attr );
        }
//        elseif ( ( $parent_id = wp_get_post_parent_id( $this->id ) ) && has_post_thumbnail( $parent_id ) ) {
//            $image = get_the_post_thumbnail( $parent_id, $size, $attr );
//        }
        else {
            $image = '';
        }

        if ( empty( $image ) ) {
            $image = '<span class="wrap-gallery-image wrapper-gallery-plus">
                          <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                      </span>';
            return $image;
        } else {
            $image = "<span class='wrap-thumbnail'>
                          <span class='glyphicon glyphicon-remove-circle remove-thumbnail'></span>
                          $image
                     </span>";
            return $image;
        }
    }
    /**
     * @return string
     */
    public function get_product_type() {
        $column_name = Fnt_ProductListCons::COLUMN_TYPE;
        $column_value = '';
        $type = $this->get_type();
        if ( isset( Fnt_ProductListCons::$column_mapping[ $column_name ][ $type ] ) ) {
            $type_name = Fnt_ProductListCons::$column_mapping[ $column_name ][ $type ];
        } else {
            $type_name = "";
        }
        if ( $type == 'variable' ) {
            $product_id   = $this->id;
            $product_type = $this->get_type();
            $column_value = '<button type="button" class="button button-variable button-primary button-show-attributes" product-id="'.$product_id.'" product-type="'.$product_type.'">'. __( 'Edit attributes', 'fnt') . '</button>';
            $column_value .= '<button type="button" class="button button-variable button-primary button-show-variation" product-id="'.$product_id.'" product-type="'.$product_type.'">'. __( 'Edit variations', 'fnt') . '</button>';
        } else if ( $type == 'simple' ) {
            $product_id   = $this->id;
            $product_type = $this->get_type();
            $column_value = '<button type="button" class="button button-variable button-primary button-show-attributes" product-id="'.$product_id.'" product-type="'.$product_type.'">'. __( 'Edit attributes', 'fnt') . '</button>';
        }
        $column_value .= "<div class='display-center'>" .
                             "<span class='product-type tips $type' title='$type_name'></span>" .
                             "<span class='product-type-name'>$type_name</span>" .
                         "</div>";
        return $column_value;
    }
    public function get_product_featured() {
        $is_featured = $this->is_featured()? '' : 'not-featured';
        return "<span class='wc-featured $is_featured tips edit-featured'>$this->featured</span>";
    }
    /**
     * @param $taxonomy
     * @param $meta
     * @param $return_type
     * @return string, list id of terms taxonomy
     */
    public function get_product_list_term_meta( $taxonomy, $meta, $return_type='string' ) {
        $terms = get_the_terms( $this->id, $taxonomy );
        if ( $return_type == 'string' ) {
            $term_meta = '';
            if ( ! empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    $term_meta .= $term->$meta . ', ';
                }
            }
            $term_meta = trim( $term_meta, ', ' );
        } else {
            $term_meta = array();
            if ( ! empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    $term_meta[] = $term->$meta;
                }
            }
        }
        return $term_meta;
    }
    /**
     * @param $price
     * @return string
     */
    public function get_formatted_product_price( $price ) {
        if ( empty( $price ) ) {
            $column_value = $price;
        } else {
            // ThienLD : fix for bug in Trello #15 The currency isn't correct position (just on loading product list. In Javascript. it's handled and worked well so far).
            $column_value = wc_price($price );
        }
        $column_value = strip_tags( $column_value );
        return $column_value;
    }
    public function get_check_box( $value, $value_when_unchecked ) {
        if ( $value == $value_when_unchecked ) {
            $checked = '';
        } else {
            $checked = 'checked';
        }
        return "<input type='checkbox' class='input-text edit-inline-checkbox' $checked />";
    }
    public static function check_sku_exists( $sku, $post_type, $product_id = '' ){
        global $wpdb;
        $wpdb->escape_by_ref($post_type);
        $wpdb->escape_by_ref($sku);
        $wpdb->escape_by_ref($product_id);
        if ( empty( $sku ) ) {
            return false;
        }
        if ( ! empty( $product_id ) ) {
            $not_in = " AND p.`ID` NOT IN({$product_id})";
        } else {
            $not_in = "";
        }
        $post_table = $wpdb->get_results("SELECT pm.`post_id` FROM {$wpdb->posts} p, {$wpdb->postmeta} pm
                                          WHERE p.`ID` = pm.`post_id` {$not_in} AND p.`post_type` IN ('$post_type', 'product_variation')
                                          AND pm.`meta_key` = '_sku' AND pm.`meta_value` = '$sku'", ARRAY_A);
        if ( ! empty( $post_table ) ) {
            return true;
        }
        return false;
    }
    public function get_product_content() {
        $button = '<button type="button" class="button button-show button-show-content" id="edit-content-'.$this->id.'" value="'.$this->id.'">Edit Description</button>';
        $column_content = Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_CONTENT];
        $value = $this->get_post_data()->$column_content;
        $input = "<textarea class='value-editor hidden' id='value-editor-content-$this->id'>$value</textarea>";
        return $button . $input;
    }
    public function get_product_excerpt() {
        $button = '<button type="button" class="button button-show button-show-excerpt" id="edit-excerpt-'.$this->id.'" value="'.$this->id.'">Edit Short Desc</button>';
        $column_excerpt = Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_EXCERPT];
        $value = $this->get_post_data()->$column_excerpt;
        $input = "<textarea class='value-editor hidden' id='value-editor-excerpt-$this->id'>$value</textarea>";
        return $button . $input;
    }
    public function get_product_id() {
        if ( ! empty( $this->old_product_id ) ) {
            return $this->old_product_id;
        }
        return $this->id;
    }
    public function is_just_add() {
        if ( ! empty( $this->just_add ) && $this->just_add == "1" ) {
            // update this product to not just add
            update_post_meta( $this->id, '_just_add', '0' );
            return true;
        }
        return false;
    }
    public function price_validation() {
        if ( $this->sale_price > $this->regular_price ) {
            return false;
        }
        return true;
    }
    public function is_have_regular_price() {
        return empty( $this->regular_price ) ? false : true;
    }

    public static function saveDefaultAttributes( $product_id, $attributes ) {
        $product_id = absint( $product_id );
        $return_value = array(); // result, message
        $previous_attribute = get_post_meta( $product_id, '_default_attributes', true );
        if ( ! is_array( $previous_attribute ) ) {
            $previous_attribute = array();
        }
        // find diff from two array
        $diff = array_merge( array_diff( $attributes, $previous_attribute ), array_diff( $previous_attribute, $attributes ) );
        if ( empty( $diff ) ) { // Default attributes not change!
            $return_value['result'] = false;
            $return_value['message'] = 'value_not_changed';
            return $return_value;
        }
        $update_result = update_post_meta( $product_id, '_default_attributes', $attributes );
        if ( $update_result == true ) {
            $return_value['result'] = $update_result;
            $return_value['message'] = __('Save default attribute success!', 'fnt');
        } else if( $update_result == false ) {
            $return_value['result'] = $update_result;
            $return_value['message'] = __('Save default attribute failed!', 'fnt');
        } else {
            $return_value['result'] = false;
            $return_value['message'] = __('Meta key did not exist', 'fnt');
        }
        return $return_value;
    }

    public static function get_cat_selection( $product_id = 0 ) {
        $taxonomy = Fnt_ProductListCons::$column_name_in_db[ Fnt_ProductListCons::COLUMN_CATEGORIES ];
        $checklist_args = array(
            'descendants_and_self' => 0,
            'selected_cats' => false,
            'popular_cats' => false,
            'walker' => null,
            'taxonomy' => $taxonomy,
            'checked_ontop' => true
        );
        ob_start();
        wp_terms_checklist( $product_id, $checklist_args );
        $str_input = '<div class="categories product_cat_select input-text-editable">';
        $str_input .= PHP_EOL;
        $str_input .= '<ul class="category_checklist form-no-clear">';
        $str_input .= ob_get_clean();
        $str_input .= '</ul></div>';

        return $str_input;
    }

    private static function convert_to_wc_format( $product_attributes = array() ) {
        $result = array();

        foreach ( $product_attributes as $product_attribute ) {
            if ( ! isset( $product_attribute['value'] ) ) {
                continue;
            }
            $result['attribute_names'][] = $product_attribute['name'];
            $result['attribute_position'][] = $product_attribute['position'];
            $result['attribute_is_taxonomy'][] = $product_attribute['is_taxonomy'];
            $result['attribute_values'][] = $product_attribute['value'];
            $result['attribute_visibility'][] = $product_attribute['is_visible'];
            $result['attribute_variation'][] = $product_attribute['is_variation'];
        }

        return $result;
    }

    public static function update_attributes( $product_id = 0, $product_attributes = array() ) {
        if ( $product_id == 0 || ! is_array( $product_attributes ) ) {
            return false;
        }

        $product_id = absint( $product_id );

        $data = self::convert_to_wc_format( $product_attributes );

        // Save Attributes
        $attributes = array();

        if ( isset( $data['attribute_names'] ) ) {

            $attribute_names  = array_map( 'stripslashes', $data['attribute_names'] );
            $attribute_values = isset( $data['attribute_values'] ) ? $data['attribute_values'] : array();

            if ( isset( $data['attribute_visibility'] ) ) {
                $attribute_visibility = $data['attribute_visibility'];
            }

            if ( isset( $data['attribute_variation'] ) ) {
                $attribute_variation = $data['attribute_variation'];
            }

            $attribute_is_taxonomy   = $data['attribute_is_taxonomy'];
            $attribute_position      = $data['attribute_position'];
            $attribute_names_max_key = max( array_keys( $attribute_names ) );

            for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
                if ( empty( $attribute_names[ $i ] ) ) {
                    continue;
                }

                $is_visible   = isset( $attribute_visibility[ $i ] ) && $attribute_visibility[ $i ] ? 1 : 0;
                $is_variation = isset( $attribute_variation[ $i ] ) && $attribute_variation[ $i ] ? 1 : 0;
                $is_taxonomy  = $attribute_is_taxonomy[ $i ] ? 1 : 0;

                if ( $is_taxonomy ) {
                    // todo: set value for default attribute to allow edit
                    if ( isset( $attribute_values[ $i ] ) ) {

                        // Select based attributes - Format values (posted values are slugs)
                        if ( is_array( $attribute_values[ $i ] ) ) {
                            $values = array_map( 'sanitize_title', $attribute_values[ $i ] );

                            // Text based attributes - Posted values are term names, wp_set_object_terms wants ids or slugs.
                        } else {
                            $values     = array();
                            $raw_values = array_map( 'wc_sanitize_term_text_based', explode( WC_DELIMITER, $attribute_values[ $i ] ) );

                            foreach ( $raw_values as $value ) {
                                $term = get_term_by( 'name', $value, $attribute_names[ $i ] );
                                if ( ! $term ) {
                                    $term = wp_insert_term( $value, $attribute_names[ $i ] );

                                    if ( $term && ! is_wp_error( $term ) ) {
                                        $values[] = $term['term_id'];
                                    }
                                } else {
                                    $values[] = $term->term_id;
                                }
                            }
                        }

                        // Remove empty items in the array
                        $values = array_filter( $values, 'strlen' );

                    } else {
                        $values = array();
                    }

                    // Update post terms
                    if ( taxonomy_exists( $attribute_names[ $i ] ) ) {
                        wp_set_object_terms( $product_id, $values, $attribute_names[ $i ] );
                    }

                    if ( $values ) {
                        // Add attribute to array, but don't set values
                        $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                            'name' 			=> wc_clean( $attribute_names[ $i ] ),
                            'value' 		=> '',
                            'position' 		=> $attribute_position[ $i ],
                            'is_visible' 	=> $is_visible,
                            'is_variation' 	=> $is_variation,
                            'is_taxonomy' 	=> $is_taxonomy
                        );
                    }

                } elseif ( isset( $attribute_values[ $i ] ) ) {

                    // Text based, possibly separated by pipes (WC_DELIMITER). Preserve line breaks in non-variation attributes.
                    $values = $is_variation ? wc_clean( $attribute_values[ $i ] ) : implode( "\n", array_map( 'wc_clean', explode( "\n", $attribute_values[ $i ] ) ) );
                    $values = implode( ' ' . WC_DELIMITER . ' ', wc_get_text_attributes( $values ) );

                    // Custom attribute - Add attribute to array and set the values
                    $attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
                        'name' 			=> wc_clean( $attribute_names[ $i ] ),
                        'value' 		=> $values,
                        'position' 		=> $attribute_position[ $i ],
                        'is_visible' 	=> $is_visible,
                        'is_variation' 	=> $is_variation,
                        'is_taxonomy' 	=> $is_taxonomy
                    );
                }

            }
        }

        if ( ! function_exists( 'attributes_cmp' ) ) {
            function attributes_cmp( $a, $b ) {
                if ( $a['position'] == $b['position'] ) {
                    return 0;
                }

                return ( $a['position'] < $b['position'] ) ? -1 : 1;
            }
        }
        uasort( $attributes, 'attributes_cmp' );

        update_post_meta( $product_id, '_product_attributes', $attributes );
        return true;
    }


    public static function get_attribute_name( $attribute_id ) {
        global $wpdb;
        $sql = "SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = $attribute_id";
        $attribute_name = $wpdb->get_results( $sql, 'ARRAY_A' );
        return $attribute_name[0]['attribute_name'];
    }
    /**
     * Add more attribute for product
     * @param $product_id
     * @param $attribute_id, the attribute want to add more
     */
    public static function add_attribute( $product_id, $attribute_id ) {
        $attribute_name = self::get_attribute_name( $attribute_id );
        // get all options of attribute
        $child_attributes = get_terms('pa_color-e', array('hide_empty' => 0));
        $term = array();
        foreach($child_attributes as $child_attribute) {
            $term[] = $child_attribute->slug;
        }
        $key = wc_attribute_taxonomy_name($attribute_name);

        //wp_set_object_terms( $product_id, $term, $key );
    }

    public static function add_more_attributes_for_all_product( $attribute_id ) {
        // Get all product id type simple and variable
        global $wpdb;
        $sql = "SELECT DISTINCT ID FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish' AND ID IN (
                SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = '_product_attributes')";
        $products_id = $wpdb->get_results($sql, 'ARRAY_A');

        // Fetch all product id, and call function add attribute
        foreach( $products_id as $product_id ) {
            self::add_attribute( $product_id['ID'], $attribute_id );
        }
    }

    /**
     * Get the product's post data.
     * Due to @deprecated 3.0.0, so this function is created to override get_post_data function by get_post()
     * @return WP_Post
     */
    public function get_post_data() {
        if ( $this->is_type( 'variation' ) ) {
            $post_data = get_post( $this->get_parent_id() );
        } else {
            $post_data = get_post( $this->get_id() );
        }
        return $post_data;
    }


    /**
     * Magic Method to solve issue of Woo 3.x <<Notice: Product properties should not be accessed directly>>
     * @return mixed
     */
    public function __get($key) {
        if ( 'post_type' === $key ) {
            return $this->post_type;
        }

        switch ( $key ) {
            case 'id' :
                $value = $this->is_type( 'variation' ) ? $this->get_parent_id() : $this->get_id();
                break;
            case 'product_type' :
                $value = $this->get_type();
                break;
            case 'product_attributes' :
                $value = isset( $this->data['attributes'] ) ? $this->data['attributes'] : '';
                break;
            case 'visibility' :
                $value = $this->get_catalog_visibility();
                break;
            case 'sale_price_dates_from' :
                return $this->get_date_on_sale_from() ? $this->get_date_on_sale_from()->getTimestamp() : '';
                break;
            case 'sale_price_dates_to' :
                return $this->get_date_on_sale_to() ? $this->get_date_on_sale_to()->getTimestamp() : '';
                break;
            case 'post' :
                $value = get_post( $this->get_id() );
                break;
            case 'download_type' :
                return 'standard';
                break;
            case 'product_image_gallery' :
                $value = $this->get_gallery_image_ids();
                break;
            case 'variation_shipping_class' :
            case 'shipping_class' :
                $value = $this->get_shipping_class();
                break;
            case 'total_stock' :
                $value = $this->get_total_stock();
                break;
            case 'downloadable' :
            case 'virtual' :
            case 'manage_stock' :
            case 'featured' :
            case 'sold_individually' :
                $value = $this->{"get_$key"}() ? 'yes' : 'no';
                break;
            case 'crosssell_ids' :
                $value = $this->get_cross_sell_ids();
                break;
            case 'upsell_ids' :
                $value = $this->get_upsell_ids();
                break;
            case 'parent' :
                $value = wc_get_product( $this->get_parent_id() );
                break;
            case 'variation_id' :
                $value = $this->is_type( 'variation' ) ? $this->get_id() : '';
                break;
            case 'variation_data' :
                $value = $this->is_type( 'variation' ) ? wc_get_product_variation_attributes( $this->get_id() ) : '';
                break;
            case 'variation_has_stock' :
                $value = $this->is_type( 'variation' ) ? $this->managing_stock() : '';
                break;
            case 'variation_shipping_class_id' :
                $value = $this->is_type( 'variation' ) ? $this->get_shipping_class_id() : '';
                break;
            case 'variation_has_sku' :
            case 'variation_has_length' :
            case 'variation_has_width' :
            case 'variation_has_height' :
            case 'variation_has_weight' :
            case 'variation_has_tax_class' :
            case 'variation_has_downloadable_files' :
                $value = true; // These were deprecated in 2.2 and simply returned true in 2.6.x.
                break;
            case Fnt_ProductListCons::COLUMN_GALLERY:
                $value = $this->get_gallery_image_ids();
                break;
            default :
                if ( in_array( $key, array_keys( $this->data ) ) ) {
                    $value = $this->{"get_$key"}();
                } else {
                    $value = get_post_meta( $this->id, '_' . $key, true );
                }
                break;
        }
        return $value;
    }

    /**
     * Handle set_prop for some special product meta data since Woocommerce 3.x
     * @param $product_id - product_id is used to get all the stored post_meta to re-save with woocommerce3.x set_props method
     * @return mixed
     */
    public function set_woocommerce_general_properties(){
        $is_featured = get_post_meta($this->get_id(),'_featured',true) == 'yes';
        if($is_featured !== $this->is_featured()){
            $this->set_featured($is_featured);
            $this->save();
        }

    }
} 