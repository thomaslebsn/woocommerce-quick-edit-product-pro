<?php
class WC_Product_Temp_Custom extends WC_Product_Custom {
    // since ver 1.1
    private $WC_Product_Variable_Custom; // use to call function of variable object
    private $WC_Product_Variable; // use to call function of variable object
    // end since ver 1.1
    /**
     * Initialize temp product.
     * @param mixed $product
     */
    public function __construct( $product ) {
        $terms = null;
        if ( is_numeric( $product ) ) {
            $terms = wp_get_post_terms($product, 'product_type');
        } elseif ( $product instanceof WC_Product ) {
            $terms = wp_get_post_terms($product->id, 'product_type');
        } elseif ( isset( $product->ID ) ) {
            $terms = wp_get_post_terms($product->ID, 'product_type');
        }
        if(!empty($terms) && sizeof($terms) > 0) {
            $this->product_type = $terms[0]->name;
        } else {
            $this->product_type = 'simple';
        }
        /**
         * Fix issue check post type must be "product" from woocommerce 3.x
         */
        $product->post_type = 'product';
        $this->WC_Product_Variable_Custom = new WC_Product_Variable_Custom($product);
        $this->WC_Product_Variable = new WC_Product_Variable($product);
        parent::__construct( $product );
    }

    // since ver 1.1
    // fake "extends WC_Product_Variable" using magic function
    // use for call function of Variable instance
    public function __call($method, $args)
    {
        if (method_exists($this->WC_Product_Variable_Custom, $method)) {
            $reflection = new ReflectionMethod($this->WC_Product_Variable_Custom, $method);
            if (!$reflection->isPublic()) {
                return call_user_func_array(array($this->WC_Product_Variable_Custom, $method), $args);
            } else if (method_exists($this->WC_Product_Variable, $method)) {
                $reflection = new ReflectionMethod($this->WC_Product_Variable, $method);
                if (!$reflection->isPublic()) {
                    throw new RuntimeException("Call to not public method " . get_class($this) . "::$method()");
                }
                return call_user_func_array(array($this->WC_Product_Variable, $method), $args);
            } else {
                throw new RuntimeException("Call to undefined method " . get_class($this) . "::$method()");
            }
        }
    }
    // end since ver 1.1

    /**
     * save all product temp
     */
    public static function save_all_temp_product() {
        global $wpdb;
        $sql = "SELECT ID FROM $wpdb->posts main 
                INNER JOIN $wpdb->postmeta pm on main.ID = pm.post_id  
                WHERE post_type = 'product' AND pm.meta_key = '".FNT_IS_IMPORTING_META_KEY."' AND pm.meta_value = '1'
                ";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        foreach($result as $item) {
            self::save_temp_product($item['ID']);
        }
    }

    /**
     * Save products
     * @param $product_ids: array product ID
     */
    public static function save_temp_products( $product_ids ) {
        foreach( $product_ids as $product_id ) {
            self::save_temp_product( $product_id );
        }
    }

    /**
     * Save product temp
     * @param $product_id: product ID
     */
    public static function save_temp_product( $product_id ) {
        $_pf = new WC_Product_Factory_Custom();
        $product = $_pf->get_product( $product_id );
        if ( ! $product ) { // if can't get Product object
            return;
        }
        if ( $product->product_type == 'variation' ) { // if Product is Variation
            return;
        }
        $real_product_id = $product->get_product_id(); // get really id from product
        $real_product_id = intval( $real_product_id ); // convert to int
        if ( $product_id != $real_product_id ) { // have old_product_id
            // update post
            $post = get_post( $product_id );
            $post->ID = $real_product_id;
            $post->post_status = 'publish';
            $post->post_type = 'product';
//            $post->post_date = date('Y-m-d H:m:s');
            // delete meta _old_product_id
            delete_post_meta( $product_id, '_old_product_id' );
            // Since woo 3.x - delete meta fnt_is_importing to determine the current product has been imported
            delete_post_meta( $product_id, FNT_IS_IMPORTING_META_KEY );
            wp_update_post($post);
            // update post meta
            $post_meta = get_post_meta( $product_id );
            $meta_not_update = array(
                '_product_attributes',
                '_upsell_ids',
                '_crosssell_ids'
            );
            if ( is_array( $post_meta ) ) {
                foreach ( $post_meta as $key => $value ) {
                    if ( ! empty( $value ) && ! in_array( $key, $meta_not_update ) ) {
                        if ( is_array( $value ) ) {
                            update_post_meta( $real_product_id, $key, $value[0] );
                        } else {
                            update_post_meta( $real_product_id, $key, $value );
                        }
                    }
                }
                // @fixbug: 16/11/2016 can't set price, in front end show free
                // update product price, base regular price and sale price
                if ( $product->product_type != 'variable' ) {
                    $price = '';
                    if ( isset( $post_meta['_sale_price'] ) && ! empty( $post_meta['_sale_price'] ) ) {
                        if ( is_array($post_meta['_sale_price']) ) {
                            $price = $post_meta['_sale_price'][0];
                        } else {
                            $price = $post_meta['_sale_price'];
                        }
                    } else if ( isset( $post_meta['_regular_price'] ) && ! empty( $post_meta['_regular_price'] ) ) {
                        if ( is_array($post_meta['_sale_price']) ) {
                            $price = $post_meta['_regular_price'][0];
                        } else {
                            $price = $post_meta['_regular_price'];
                        }
                    }
                    update_post_meta( $real_product_id, '_price', $price );
                }
            }
            // update product cat
            $product_cat = wp_get_object_terms( $product_id, 'product_cat' );
            $product_cat_name = array();
            foreach ( $product_cat as $value ) {
                $product_cat_name[] = $value->name;
            }
            if ( ! empty( $product_cat_name ) ) {
                wp_set_object_terms( $real_product_id, $product_cat_name, 'product_cat' );
            }

            // update product tag
            $product_tag = wp_get_object_terms( $product_id, 'product_tag' );
            $product_tag_name = array();
            foreach ( $product_tag as $value ) {
                $product_tag_name[] = $value->name;
            }
            if ( ! empty( $product_tag_name ) ) {
                wp_set_object_terms( $real_product_id, $product_tag_name, 'product_tag' );
            }
            /**
             * Sync product meta data in some special cases since woocommerce 3.x
             */
            $_pf = new WC_Product_Factory_Custom();
            $currentProduct = $_pf->get_product( $real_product_id );
            $currentProduct->set_woocommerce_general_properties();
//            if ( $product->product_type == 'variable' ) {
//                // update variations
//                $exists_variations_id = self::get_list_variations_id( $real_product_id );
//                $new_variations_id = self::get_list_variations_id( $product_id );
//
//                foreach ( $exists_variations_id as $variation_id ) {
//                    self::update_parent_id( $variation_id, $product_id );
//                }
//
//                foreach ( $new_variations_id as $variation_id ) {
//                    self::update_parent_id( $variation_id, $real_product_id );
//                }
//            }

            // delete temp product
            self::delete_permanently_product( $product_id );
        } else { // import new product
            $posts['ID'] = $product_id;
            $posts['post_status'] = 'publish';
            $posts['post_type'] = 'product';
            wp_update_post($posts);
            // Since woo 3.x - delete meta fnt_is_importing to determine the current product has been imported
            delete_post_meta( $product_id, FNT_IS_IMPORTING_META_KEY );
            /**
             * Sync product meta data in some special cases since woocommerce 3.x
             */
            $_pf = new WC_Product_Factory_Custom();
            $currentProduct = $_pf->get_product( $product_id );
            $currentProduct->set_woocommerce_general_properties();
        }
    }

    public static function get_list_variations_id( $parent_id ) {
        global $wpdb;
        $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_parent = ' . $parent_id . ' AND post_status IN("private", "publish")
            AND post_type="product_variation" ORDER BY menu_order ASC, ID DESC';
        $variations = $wpdb->get_results( $sql, 'ARRAY_A' );
        if ( ! empty( $variations ) && is_array( $variations ) ) {
            foreach ( $variations as $key => $item ) {
                $variations[$key] = $item['ID'];
            }
            return $variations;
        }
        return array();
    }

    public static function update_parent_id( $product_id, $new_parent_id ) {
        global $wpdb;
        return $wpdb->update(
            $wpdb->posts,
            array( 'post_parent' => $new_parent_id ),
            array( 'ID' => $product_id ),
            array( '%d' ),
            array( '%d' )
        );
    }

    /**
     * Update multi product temp
     * @param array $product_list: array product
     * @param array $error_array
     */
    public static function update_products($product_list = array(), &$error_array = array()){
        if ( ! empty( $product_list ) && is_array( $product_list ) ) {
            foreach ( $product_list as $product ) {
                if ( empty( $product ) ) {
                    continue;
                }
                if(!isset($product['modifying_product']) || $product['modifying_product'] != '1'){
                    self::save_temp_product($product['id']);
                    continue;
                }
                $error ='';
                $result = self::update_product($product, $error);
                self::save_temp_product($product['id']);
                if(!empty($error)){
                    array_push($error_array, $error);
                }
            }
        } else {
            array_push( $error_array, __('Data input not correct format!', 'fnt'));
        }
    }

    /**
     * This function is add a product as type 'temp' to database
     * Have two case when adding:
     *  1. Add product already exists in database
     *  - Then we duplicate new product with same data as product exists.
     *  - And update data of fields change in data of excel file imported.
     *  - Finally we have a new product merge data of product exists and data of excel file imported.
     *  2. Add new product with data in excel file: form a template of each product type
     *  - We create new post and insert data for postmeta and term data.
     *  - Finally we have a new product with data in excel file imported.
     * @param array $product: content post data, meta data, term data
     * @param string $error: use to report error
     * @return int|WP_Error: return product id if success, else return an error
     */
    public static function add_product($product = array(), &$error = '') {
        $product = self::format_product_before_insert($product);
        $product_type = isset( $product['terms'] ) && isset( $product['terms']['product_type'] ) ? $product['terms']['product_type'] : '';
        $posts = array();
        $post_meta = array();
        $terms = array();
        $product_id = '';
        $product_attributes = array();
        // export product in database
        if(isset($product['posts']) && is_array($product['posts'])) {
            $posts = $product['posts'];
            if(isset($posts['ID'])) {
                $product_id = $posts['ID'];
            }
            // extra fields default
            $posts['post_author'] = get_current_user_id();
            $posts['post_status'] = 'pending';
            /**
             * Fix issue check post type must be "product" from woocommerce 3.x
             */
            $posts['post_type'] = 'product';
            if ( empty( $product_id ) ) {
                $posts['post_date'] = date('Y-m-d H:m:s');
            }
        }

        // Get attributes of product and add all attribute options to database
        // Get attributes of product
        if(isset($product['postmeta']) && is_array($product['postmeta'])) {
            $post_meta = $product['postmeta'];
            // if product not exists in database, import from template for each product type
            if ( empty( $product_id ) ) {
                $product_attributes = isset( $post_meta[ '_product_attributes' ] ) ? $post_meta[ '_product_attributes' ] : array();
                $post_meta[ '_product_attributes' ] = self::format_attributes_before_insert( $product_attributes );
            } else {
                $post_meta['_old_product_id'] = $product_id;
            }

            if ( isset( $post_meta['_stock'] ) && is_numeric( $post_meta['_stock'] ) ) {
                $stock_quantity = intval( $post_meta['_stock'] );
                // save stock quantity as integer number
                $post_meta['_stock'] = $stock_quantity;
            }
        }
        // Add all attribute options to database
        if(isset($product['terms']) && is_array($product['terms'])) {
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
        if(!empty($product_id)) {
            // product had exists in database
            // duplicate product with include class
            $pd = new WC_Admin_Duplicate_Product();
            $post = wc_get_product( $product_id );
            add_filter( 'woocommerce_duplicate_product_exclude_children', array( 'WC_Product_Temp_Custom', 'ignore_duplicate_variations' ) );
            $post_id = $pd->product_duplicate($post);
            $posts['ID'] = $post_id;
            wp_update_post($posts);
        } else {
            $posts['ID'] = null; // for create new product
            $post_id = wp_insert_post( $posts );

            // create default product meta fields
            self::create_post_meta_template( $post_id );
        }
        // update terms of product
        foreach($terms as $key=>$value) {
            if(is_array($value)) {
                // if it is categories format value of array to integer
                if($key == 'product_cat') {
                    $value = array_map( 'intval', $value );
                    $value = array_unique( $value );
                }
                $array_terms = array();
                foreach($value as $term) {
                    $array_terms[] =  $term;
                }
                wp_set_object_terms( $post_id, $array_terms, $key );
            } else {
                wp_set_object_terms( $post_id, $value, $key );
            }
        }
        // update postmeta of product
        foreach ( $post_meta as $key => $value ) {
            // if value is not null, then update data
            if ( $value !== null ) {
                // must update data of stock quantity first to change correct stock status
                // because stock status is sync with stock quantity
                if ( $key == '_stock_status' && isset( $post_meta['_stock'] ) ) {
                    update_post_meta( $post_id, '_stock', $post_meta['_stock'] );
                }
                update_post_meta( $post_id, $key, $value );
            }
        }

        // @fixbug: can't set price, in front end show free
        // update product price, base regular price and sale price
        if ( ! empty ( $product_type ) && $product_type != 'variable' ) { // Not update price of variable product
            $price = '';
            if ( isset( $post_meta[ '_sale_price' ] ) && ! empty( $post_meta[ '_sale_price' ] ) ) {
                $price = $post_meta[ '_sale_price' ];
            } else if ( isset( $post_meta[ '_regular_price' ] ) && ! empty( $post_meta[ '_regular_price' ] ) ) {
                $price = $post_meta[ '_regular_price' ];
            }
            update_post_meta( $post_id, '_price', $price );
        }
        // end update price

        /**
         * Fix issue check post type must be "product" from woocommerce 3.x
         */
        update_post_meta($post_id, FNT_IS_IMPORTING_META_KEY, '1');
        return $post_id;
    }

    public static function ignore_duplicate_variations( $ignore ) {
        return true;
    }

    /**
     * Create fields for product meta
     * @param $post_id: ID of product need create meta data
     */
    private static function create_post_meta_template( $post_id ) {
        if ( is_numeric( $post_id ) && $post_id > 0 ) {
            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, '_stock_status', 'outofstock' );
            update_post_meta( $post_id, 'total_sales', '0' );
            update_post_meta( $post_id, '_downloadable', 'no' );
            update_post_meta( $post_id, '_virtual', 'no' );
            update_post_meta( $post_id, '_regular_price', '0' );
            update_post_meta( $post_id, '_sale_price', '0' );
            update_post_meta( $post_id, '_purchase_note', '' );
            update_post_meta( $post_id, '_featured', 'no' );
            update_post_meta( $post_id, '_weight', '' );
            update_post_meta( $post_id, '_length', '');
            update_post_meta( $post_id, '_width', '' );
            update_post_meta( $post_id, '_height', '');
            update_post_meta( $post_id, '_sku', '' );
            update_post_meta( $post_id, '_product_attributes', array() );
            update_post_meta( $post_id, '_sale_price_dates_from', '');
            update_post_meta( $post_id, '_sale_price_dates_to', '' );
            update_post_meta( $post_id, '_price', '0' );
            update_post_meta( $post_id, '_sold_individually', 'no' );
            update_post_meta( $post_id, '_manage_stock', 'no' );
            update_post_meta( $post_id, '_backorders', 'no' );
            update_post_meta( $post_id, '_stock', '0' );
            update_post_meta( $post_id, '_download_limit', '' );
            update_post_meta( $post_id, '_download_expiry', '' );
            update_post_meta( $post_id, '_download_type', '' );
            update_post_meta( $post_id, '_product_image_gallery', '' );
        }
    }

    /**
     * Delete all product have type 'temp'
     */
    public static function delete_temp_data() {
        global $wpdb;
        $sql = "SELECT ID 
                FROM {$wpdb->posts} main INNER JOIN $wpdb->postmeta pm on main.ID = pm.post_id  
                WHERE main.post_type = 'product' AND pm.meta_key = '".FNT_IS_IMPORTING_META_KEY."' AND pm.meta_value = '1'";
        $products = $wpdb->get_results($sql);
        if(is_array($products)) {
            foreach($products as $product) {
                self::delete_permanently_product($product->ID);
            }
        }
    }

    /**
     * Delete multi product
     * @param $product_ids: array product ID
     */
    public static function delete_temp_products($product_ids) {
        foreach($product_ids as $product_id) {
            self::delete_permanently_product($product_id);
        }
    }

    /**
     * Delete a product
     * @param int $product_id: product ID
     * @param string $error
     * @return array|false|int|WP_Post
     */
    public static function delete_permanently_product($product_id = 0, &$error='') {
        if($product_id === 0) {
            return Fnt_ProductAddNewItemInModel::product_not_exists;
        }
        //self::delete_term_relationships($product_id);
//        self::save_temp_product($product_id);// todo: find new way
        $posts['ID'] = $product_id;
//        $posts['post_status'] = 'publish';
        $posts['post_type'] = 'product';
        wp_update_post( $posts );
        return wp_delete_post( $product_id, true );
    }

    /**
     * todo: check this is need?
     * Delete tag of product, only delete if no product use this tag
     * @param $product_id
     */
    public static function delete_terms_tag($product_id) {
        $terms = get_the_terms($product_id, 'product_tag');
        if(is_array($terms)) {
            foreach($terms as $term) {
                self::delete_term_tag($product_id, $term->term_id);
            }
        }
    }

    /**
     * Delete term if only product have product_id use this term
     * @param $product_id
     * @param $term_id
     * @return bool|false|int
     */
    public static function delete_term_tag($product_id, $term_id) {
        global $wpdb;
        $wpdb->escape_by_ref($product_id);
        $wpdb->escape_by_ref($term_id);
        // check term is just use for current product
        // get product used this term
        $sql = "SELECT COUNT(DISTINCT({$wpdb->term_relationships}.`object_id`)) FROM {$wpdb->term_relationships}
                JOIN `wp_term_taxonomy` ON {$wpdb->term_relationships}.`term_taxonomy_id` = `wp_term_taxonomy`.`term_taxonomy_id`
                JOIN `wp_terms` ON `wp_term_taxonomy`.`term_id` = `wp_terms`.`term_id`
                WHERE `wp_terms`.`term_id` = ".$term_id." AND {$wpdb->term_relationships}.`object_id` NOT IN (".$product_id.")";
        $product_used_this_term = $wpdb->get_var($sql);
        // if don't other product used this term, then delete it
        if($product_used_this_term < 0) {
            $sql = "DELETE FROM {$wpdb->terms} WHERE {$wpdb->terms}.`term_id` = " . $term_id;
            return $wpdb->query($sql);
        }
        return false;
    }

    /**
     * Since ver 1.0.4
     * Count all temp product
     */
    public static function count_product_temp() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(DISTINCT ID) AS num_temp 
                                FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id   
                                WHERE post_type = 'product' AND pm.meta_key = '".FNT_IS_IMPORTING_META_KEY."' AND pm.meta_value = '1'" );
    }
} 