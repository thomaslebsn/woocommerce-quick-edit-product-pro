<?php
    class WC_Product_Variation_Custom extends WC_Product_Custom {
        private $WC_Product_Variation;
        /**
         * Loads required variation data.
         *
         * @param int $variation ID of the variation to load
         * @param array $args Array of the arguments containing parent product data
         */
        public function __construct( $variation, $args = array() ) {
            $this->WC_Product_Variation = new WC_Product_Variation($variation, $args = array());
            if ( is_object( $variation ) ) {
                $this->variation_id = absint( $variation->ID );
            } else {
                $this->variation_id = absint( $variation );
            }

            /* Get main product data from parent (args) */
            $this->id = ! empty( $args['parent_id'] ) ? intval( $args['parent_id'] ) : wp_get_post_parent_id( $this->variation_id );

            // The post doesn't have a parent id, therefore its invalid.
            if ( empty( $this->id ) ) {
                return;
            }

            $this->product_type = 'variation';
            $this->parent       = ! empty( $args['parent'] ) ? $args['parent'] : wc_get_product( $this->id );
            $this->post         = ! empty( $this->parent->post ) ? $this->parent->post : array();
            // @since 1.1
            parent::__construct( $variation );
        }
        // fake "extends WC_Product_Variation" using magic function
        public function __call($method, $args) {
            if (method_exists($this->WC_Product_Variation, $method)) {
                $reflection = new ReflectionMethod($this->WC_Product_Variation, $method);
                if (!$reflection->isPublic()) {
                    throw new RuntimeException("Call to not public method ".get_class($this)."::$method()");
                }
                return call_user_func_array(array($this->WC_Product_Variation, $method), $args);
            } else {
                throw new RuntimeException("Call to undefined method ".get_class($this)."::$method()");
            }
        }

        /*
         * Add new variation product
         */
//        public static function add_product($product = array(), &$error='') {
//
//        }
        public static function get_attribute_slug_by_name($taxonomy, $term_name) {
            global $wpdb;
            $wpdb->escape_by_ref($taxonomy);
            $wpdb->escape_by_ref($term_name);
            $sql = "SELECT `wp_terms`.`slug` FROM `wp_terms` JOIN `wp_term_taxonomy` ON `wp_terms`.`term_id` = `wp_term_taxonomy`.`term_id`
                WHERE `wp_term_taxonomy`.`taxonomy` = '$taxonomy' AND `wp_terms`.`name` = '$term_name'";
            $result = $wpdb->get_var($sql);
            return $result;
        }
        /**
         * Add variation product
         * @param int $parent_id, id of product variable: parent of this product variation
         * @param array $product, information of variation need to add
         * @return bool|int, the id new variation product if add success or return false if add failed
         */
        public static function add_variation( $parent_id = 0, $product = array() ) {
            // Add variation post, must be have parent product id
            // if don't have parent id, add fail
            if($parent_id == 0) {
                return false;
            }
            //        // Get value attributes
            //        $product_attributes = isset( $product[ Fnt_ProductListCons::COLUMN_ATTRIBUTE ] ) ? $product[ Fnt_ProductListCons::COLUMN_ATTRIBUTE ] : array();
            //
            //        $product_attribute_value = array();
            //        foreach($product as $key=>$value) {
            //            if(in_array($key, $product_attributes)) {
            //                if(self::get_attribute_slug_by_name(wc_attribute_taxonomy_name($key), $value) == null) {
            //                    $product_attribute_value[$key] = $value;
            //                } else {
            //                    $product_attribute_value[$key] = self::get_attribute_slug_by_name(wc_attribute_taxonomy_name($key), $value);
            //                }
            //            }
            //        }

            $product = self::format_product_before_insert($product);
            $product_attribute_value = isset ( $product['attribute_value'] ) ? $product['attribute_value'] : array();
            $posts = array();

            // prepare data to add new variation product
            if(isset($product['posts']) && is_array($product['posts'])) {
                $posts = $product['posts']; // get post in data input
                // extra fields default
                $posts['post_title'] = 'Product #' . $parent_id . ' Variation';
                $posts['post_content'] = '';
                $posts['post_status'] = 'publish';
                $posts['post_author'] = get_current_user_id();
                $posts['post_parent'] = $parent_id;
                $posts['post_type'] = 'product_variation';
                $posts['menu_order']   = 0;
                $posts['ID'] = null; // for create new product
            }
            $variation_id = wp_insert_post( $posts );

            if ( $variation_id ) {
                // insert meta data for variation
                if(isset($product['postmeta']) && is_array($product['postmeta'])) {
                    $post_meta = $product['postmeta'];
                    $variation_data   = array();
                    $variation_meta_fields = array(
                        '_sku'                   => '',
                        '_stock'                 => '',
                        '_regular_price'         => '',
                        '_sale_price'            => '',
                        '_price'                 => '',
                        '_weight'                => '',
                        '_length'                => '',
                        '_width'                 => '',
                        '_height'                => '',
                        '_download_limit'        => '',
                        '_download_expiry'       => '',
                        '_downloadable_files'    => '',
                        '_downloadable'          => '',
                        '_virtual'               => '',
                        '_thumbnail_id'          => '',
                        '_sale_price_dates_from' => '',
                        '_sale_price_dates_to'   => '',
                        '_manage_stock'          => '',
                        '_stock_status'          => '',
                        '_backorders'            => null,
                        '_tax_class'             => null,
                        '_variation_description' => ''
                    );

                    // Merge data from data input and default value
                    foreach ( $variation_meta_fields as $field => $value ) {
                        $variation_data[ $field ] = isset( $post_meta[ $field ] ) ? $post_meta[ $field ] : $value;
                    }

                    // Formatting
                    $variation_data['_regular_price'] = wc_format_localized_price( $variation_data['_regular_price'] );
                    $variation_data['_sale_price']    = wc_format_localized_price( $variation_data['_sale_price'] );
                    $variation_data['_weight']        = wc_format_localized_decimal( $variation_data['_weight'] );
                    $variation_data['_length']        = wc_format_localized_decimal( $variation_data['_length'] );
                    $variation_data['_width']         = wc_format_localized_decimal( $variation_data['_width'] );
                    $variation_data['_height']        = wc_format_localized_decimal( $variation_data['_height'] );
                    $variation_data['_thumbnail_id']  = absint( $variation_data['_thumbnail_id'] );

                    // Add variation info for variation product
                    foreach($variation_data as $meta_key => $value) {
                        update_post_meta( $variation_id, $meta_key, $value );
                    }

                    // Add attributes for variation product
                    foreach($product_attribute_value as $meta_key => $value) {
                        update_post_meta( $variation_id, "attribute_".wc_attribute_taxonomy_name($meta_key), $value );
                    }

                    // since ver 1.1
                    // @fixbug: can't set price, in front end show free
                    // update product price, base regular price and sale price
                    $price = '';
                    if ( isset( $post_meta['_sale_price'] ) && ! empty( $post_meta['_sale_price'] ) ) {
                        $price = $post_meta['_sale_price'];
                    } else if ( isset( $post_meta['_regular_price'] ) && ! empty( $post_meta['_regular_price'] ) ) {
                        $price = $post_meta['_regular_price'];
                    }
                    update_post_meta( $variation_id, '_price', $price );
                    // end update price

                    // make manage stock change of variation product correct with default flow of WC
                    if ( isset( $post_meta['_manage_stock'] ) ) {
                        // if uncheck manage stock of variation, remove 2 fields of postmeta
                        if ( $post_meta['_manage_stock'] == 'no' ) {
                            delete_post_meta( $variation_id, '_stock' );
                            delete_post_meta( $variation_id, '_backorders' );
                        }
                        // if check manage stock of variation, add 2 fields with default value
                        if ( $post_meta['_manage_stock'] == 'yes' ) {
                            update_post_meta( $variation_id, '_stock', isset( $post_meta['_stock'] ) ? $post_meta['_stock'] : 0 );
                            update_post_meta( $variation_id, '_backorders', isset( $post_meta['_backorders'] ) ? $post_meta['_backorders'] : 'no' );
                        }
                    }
                    // use to make stock status correctly
                    if ( isset ( $post_meta['_stock_status'] ) ) {
                        update_post_meta( $variation_id, '_stock_status', $post_meta['_stock_status'] );
                    }

                    // end since ver 1.1
                }

                // since ver 1.1
                // update product terms
                if ( isset( $product['terms'] ) && is_array( $product['terms'] ) ) {
                    $terms = $product[ 'terms' ];

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
                            wp_set_object_terms( $variation_id, $array_terms, $key );
                        } else {
                            wp_set_object_terms( $variation_id, $value, $key );
                        }
                    }
                }
                // end since ver 1.1
            }
            return $variation_id;
        }

        public static function add_blank_variation( $parent_id ) {
            global $post;
            $post = get_post( $parent_id ); // Set $post global so its available like within the admin screens

            $variation = array(
                'post_title'   => 'Product #' . $parent_id . ' Variation',
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
                'post_parent'  => $parent_id,
                'post_type'    => 'product_variation',
                'menu_order'   => -1
            );

            $variation_id = wp_insert_post( $variation );
            if ( $variation_id ) {
                $variation_fields = array(
                    '_sku'                   => '',
                    '_stock'                 => '',
                    '_regular_price'         => '',
                    '_sale_price'            => '',
                    '_weight'                => '',
                    '_length'                => '',
                    '_width'                 => '',
                    '_height'                => '',
                    '_download_limit'        => '',
                    '_download_expiry'       => '',
                    '_downloadable_files'    => '',
                    '_downloadable'          => '',
                    '_virtual'               => '',
                    '_thumbnail_id'          => '',
                    '_sale_price_dates_from' => '',
                    '_sale_price_dates_to'   => '',
                    '_manage_stock'          => '',
                    '_stock_status'          => '',
                    '_variation_description' => '',
                    '_price'                 => '',
                    '_backorders'            => null,
                    '_tax_class'             => null
                );

                // Update default meta value for variation product
                foreach( $variation_fields as $meta_key => $value ) {
                    update_post_meta( $variation_id, $meta_key, $value );
                }
                // mark product is just add
                update_post_meta( $variation_id, '_just_add', '1' );

                return true;
            } else {
                return false;
            }
        }

        /**
         * Since ver 1.1
         * Get attributes selection box
         *
         * @return string
         */
        public function get_selection_variation_attributes() {
            $variation_data = $this->get_variation_attributes();
            $attributes     = $this->parent->get_attributes();
            $description    = array();
            $return         = '';

            if ( is_array( $variation_data ) ) {

                foreach ( $attributes as $attribute ) {
                    $product_attr = 'attribute_' . sanitize_title( $attribute['name'] );
                    $html_attrs = 'product-id="' . $this->id . '"';
                    $html_attrs .= 'class="variation-attributes"';
                    $attr_select = '<select id="' . $product_attr . '_' . $this->id . '" name="' . $product_attr . '" ' . $html_attrs . '>';
                    // Only deal with attributes that are variations
                    if ( ! $attribute[ 'is_variation' ] ) {
                        continue;
                    }

                    $variation_selected_value = isset( $variation_data[ 'attribute_' . sanitize_title( $attribute[ 'name' ] ) ] ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute[ 'name' ] ) ] : '';
                    $description_name         = esc_html( wc_attribute_label( $attribute[ 'name' ] ) );
                    $description_value        = __( 'Any '. $description_name .'&hellip;', 'woocommerce' );

                    $attr_select .= '<option value="">'. $description_value .'</option>';

                    // Get terms for attribute taxonomy or value if its a custom attribute
                    if ( $attribute[ 'is_taxonomy' ] ) {

                        $post_terms = wp_get_post_terms( $this->parent->id, $attribute[ 'name' ] );

                        foreach ( $post_terms as $term ) {
                            $description_value = esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) );
                            if ( $variation_selected_value === $term->slug ) {
                                $selected = 'selected="selected"';
                            } else {
                                $selected = '';
                            }
                            $attr_select .= '<option ' . $selected . ' value="' . esc_attr($term->slug) . '">'. $description_value .'</option>';
                        }

                    } else {

                        $options = wc_get_text_attributes( $attribute[ 'value' ] );

                        foreach ( $options as $option ) {
                            $selected = 'selected="selected"';
                            if ( sanitize_title( $variation_selected_value ) === $variation_selected_value ) {
                                if ( $variation_selected_value !== sanitize_title( $option ) ) {
                                    $selected = '';
                                }
                            } else {
                                if ( $variation_selected_value !== $option ) {
                                    $selected = '';
                                }
                            }

                            $description_value = esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) );

                            $attr_select .= '<option ' . $selected . ' value="' . esc_attr( $option ) . '">'. $description_value .'</option>';
                        }
                    }
                    $attr_select .= '</select>';

                    $description[] = '<span>' . $description_name . ': ' . $attr_select . '</span>';
                }

                $return .= implode( '', $description );
            }

            return $return;
        }

        /**
         * Delete multi products variation
         * @param array $list_id
         * @return bool
         */
        public static function delete_permanently_multiple_products( $list_id = array() ) {
            if ( ! is_array( $list_id ) || empty( $list_id ) || ! isset( $_POST['product_id'] ) ) {
                return false;
            } else {
                $parent_product_id = $_POST['product_id'];
                foreach ( $list_id as $id ) {
                    $_pf = new WC_Product_Factory_Custom();
                    $product = $_pf->get_product( $id );
                    $product::delete_permanently_product( $id );
                }
                // call function to sync product variation with variable
                WC_Product_Variable::sync( $parent_product_id );
                wc_delete_product_transients( $parent_product_id );

                return true;
            }
        }
    }