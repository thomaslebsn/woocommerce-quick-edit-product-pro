<?php
    class WC_Product_Variable_Custom extends WC_Product_Custom {
        private $WC_Product_Variable;
        /**
         * Initialize variable product.
         *
         * @param mixed $product
         */
        public function __construct( $product ) {
            $this->WC_Product_Variable = new WC_Product_Variable($product);
            $this->product_type = 'variable';
            parent::__construct( $product );
        }
        // fake "extends WC_Product_Variable" using magic function
        public function __call($method, $args) {
            if (method_exists($this->WC_Product_Variable, $method)) {
                $reflection = new ReflectionMethod($this->WC_Product_Variable, $method);
                if (!$reflection->isPublic()) {
                    throw new RuntimeException("Call to not public method ".get_class($this)."::$method()");
                }
                return call_user_func_array(array($this->WC_Product_Variable, $method), $args);
            } else {
                throw new RuntimeException("Call to undefined method ".get_class($this)."::$method()");
            }
        }

        /**
         * Get an array of available variations for the current product.
         * @return array
         */
        public function get_available_variations() {
            global $wpdb;
            $available_variations = array();
            $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_parent = ' . $this->id . ' AND post_status IN("private", "publish")
            AND post_type="product_variation" ORDER BY menu_order ASC, ID DESC';
            $variations = $wpdb->get_results( $sql, 'ARRAY_A' );
            if ( ! empty( $variations ) && is_array( $variations ) ) {
                foreach ( $variations as $key => $item ) {
                    $variations[$key] = $item['ID'];
                }
            }

            if ( is_array( $variations ) ) {
                foreach ( $variations as $child_id ) {
                    $variation = $this->get_child( $child_id );
                    $available_variations[] = $variation;
                }
            }

            return $available_variations;
        }

        /**
         * get_child function.
         *
         * @param mixed $child_id
         * @return WC_Product WC_Product or WC_Product_variation
         */
        public function get_child( $child_id ) {
            $_pf = new WC_Product_Factory_Custom();
            $product = $_pf->get_product( $child_id );
            return $product;
        }

        /**
         * Since ver 1.1
         * Get attributes selection box
         *
         * @return string
         */
        public function get_selection_variation_attributes() {
            $attributes = maybe_unserialize( get_post_meta( $this->id, '_product_attributes', true ) );
                $return = '<div class="variations-default-attributes">';
                    $return .= '<strong>'.__( 'Default Form Values', 'fnt' ).':</strong>';

                        $default_attributes = maybe_unserialize( get_post_meta( $this->id, '_default_attributes', true ) );

                        foreach ( $attributes as $attribute ) {

                            // Only deal with attributes that are variations
                            if ( ! $attribute['is_variation'] ) {
                                continue;
                            }

                            // Get current value for variation (if set)
                            $variation_selected_value = isset( $default_attributes[ sanitize_title( $attribute['name'] ) ] ) ? $default_attributes[ sanitize_title( $attribute['name'] ) ] : '';

                            // Name will be something like attribute_pa_color
                            $return .= '<select class="default-attribute default_attribute_' . sanitize_title( $attribute['name'] ) . '" attribute-name="' . sanitize_title( $attribute['name'] ) . '" data-current="' . esc_attr( $variation_selected_value ) . '"><option value="">' . __( 'No default', 'fnt' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';

                            // Get terms for attribute taxonomy or value if its a custom attribute
                            if ( $attribute['is_taxonomy'] ) {
                                $post_terms = wp_get_post_terms( $this->id, $attribute['name'] );

                                foreach ( $post_terms as $term ) {
                                    if ( is_object( $term ) ) {
                                        $return .= '<option ' . selected( $variation_selected_value, $term->slug, false ) . ' value="' . esc_attr( $term->slug ) . '">' . apply_filters( 'woocommerce_variation_option_name', esc_html( $term->name ) ) . '</option>';
                                    }
                                }
                            } else {
                                $options = wc_get_text_attributes( $attribute['value'] );

                                foreach ( $options as $option ) {
                                    $selected = sanitize_title( $variation_selected_value ) === $variation_selected_value ? selected( $variation_selected_value, sanitize_title( $option ), false ) : selected( $variation_selected_value, $option, false );
                                    $return .= '<option ' . $selected . ' value="' . esc_attr( $option ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) )  . '</option>';
                                }
                            }

                            $return .= '</select>';
                        }
                    $return .= '</div>';

            return $return;
        }

        /**
         * Get sale price and regular price when change variation price and sync
         * @param $product_id
         * @return array
         */
        public static function get_ajax_price_sync( $product_id ) {
            $_pf = new WC_Product_Factory_Custom();
            $currentProduct = $_pf->get_product( $product_id );
            $sale_price_html = Fnt_CustomProductList::render_table_cell_value( $currentProduct, Fnt_ProductListCons::COLUMN_SALE_PRICE );
            $regular_price_html = Fnt_CustomProductList::render_table_cell_value( $currentProduct, Fnt_ProductListCons::COLUMN_REGULAR_PRICE );
            $result = array(
                'sale_price_html' => $sale_price_html,
                'regular_price_html' => $regular_price_html
            );
            return $result;
        }
    }