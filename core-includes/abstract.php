<?php

abstract class Fnt_Core
{

    protected $page_hook_name = null;

    /**
     * @return mixed
     */
    public function get_page_hook_name()
    {
        return $this->page_hook_name;
    }

    /**
     * @param mixed $page_hook_name
     */
    public function set_page_hook_name($page_hook_name)
    {
        $this->page_hook_name = $page_hook_name;
    }

    public function __construct()
    {

    }

    /*
     * Begin make dropdown list
     */
    private static function get_terms_child($type, $parent, $grade)
    {
        $grade++;
        $child_terms = get_terms($type, array('parent' => $parent, 'hide_empty' => 0));
        $result = array();
        if (!empty($child_terms)) {
            foreach ($child_terms as $child_term) {
                $result[$child_term->term_id] = (array)$child_term;
                // set grade
                $result[$child_term->term_id]['grade'] = $grade;
                $child = self::get_terms_child($type, $child_term->term_id, $grade);
                if (!empty($child)) {
                    $result[$child_term->term_id]['child'] = self::get_terms_child($type, $child_term->term_id, $grade);
                }
            }
        }
        return $result;
    }

    private static function get_terms_grade($type)
    {
        $start_grade = -1;
        return self::get_terms_child($type, 0, $start_grade);
    }

    /**
     * @param $mode
     * @param $product_term
     * @return string
     */
    private static function get_prefix($mode, $product_term)
    {
        $prefix = '';
        if ($mode == 'export') {
            $prefix = $product_term['term_id'] . ':';
        }
        $grade = intval($product_term['grade']);
        for ($i = 1; $i <= $grade; $i++) {
            $prefix .= '  ';
        }
        return $prefix;
    }

    private static function get_terms_grade_array_name($array, $mode)
    {
        $result = array();
        foreach ($array as $item) {
            if (isset($item['grade']) && is_numeric($item['grade'])) {
                $prefix = self::get_prefix($mode, $item);
                $result[] = $prefix . $item['name'];
            }
            if (!empty($item['child']) && is_array($item['child'])) {
                $result = array_merge($result, self::get_terms_grade_array_name($item['child'], $mode));
            }
        }
        return $result;
    }

    /**
     * @param $type
     * @param string $mode
     * @return array, get terms by type
     */
    public static function get_dropdownlist($type, $mode = 'export')
    {
        $array = array();
        switch ($type) {
            case Fnt_ProductListCons::COLUMN_CATEGORIES:
                $terms_graded = self::get_terms_grade($type);
                $array = self::get_terms_grade_array_name($terms_graded, $mode);
                break;
            case Fnt_ProductListCons::COLUMN_SHIPPING_CLASS:
                $mode = 'export_shipping_class';
                $terms_graded = self::get_terms_grade($type);
                $array = self::get_terms_grade_array_name($terms_graded, $mode);
                break;
            default:
                if (isset(Fnt_ProductListCons::$column_mapping[$type])) {
                    $array = Fnt_ProductListCons::$column_mapping[$type];
                }
                if (Fnt_Core::attribute_name_exists($type)) {
                    $terms = get_terms(wc_attribute_taxonomy_name($type), array('hide_empty' => 0));
                    foreach ($terms as $term) {
                        $array[$term->slug] = $term->name;
                    }
                }
                break;
        }
        return $array;
    }

    /*
     * End make dropdown list
     */

    public static function convert_array_to_object($array = array())
    {
        $array_temp = array();
        array_push($array_temp, $array);
        $array = json_decode(json_encode($array_temp));
        return $array;
    }

    /**
     * @param $term_id , id of category name
     * @return array|null|object product have term id input
     */
    public static function get_products_by_term_id($term_id)
    {
        global $wpdb;
        $wpdb->escape_by_ref($term_id);
        $sql = "SELECT post.ID
                FROM (($wpdb->posts post LEFT JOIN $wpdb->term_relationships termre ON post.ID = termre.`object_id`)
	            LEFT JOIN $wpdb->term_taxonomy termta ON termre.`term_taxonomy_id` = termta.`term_taxonomy_id`)
	            LEFT JOIN $wpdb->terms term ON termta.`term_id` = term.term_id
                WHERE term.term_id = '" . $term_id . "'
                AND post.post_type = '" . Fnt_Url_Handler::get_product_type() . "'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }

    /**
     * get products unset categories
     * @return array|null|object
     */
    public static function get_products_uncategorized()
    {
        global $wpdb;
        $default_post_type = FNT_POST_TYPE;
        $sql = "SELECT post.ID
                FROM (($wpdb->posts post LEFT JOIN $wpdb->term_relationships termre ON post.ID = termre.object_id)
                LEFT JOIN $wpdb->term_taxonomy termta ON (termre.term_taxonomy_id = termta.term_taxonomy_id AND termta.taxonomy = 'product_cat'))
                WHERE post.post_type = '$default_post_type'
                GROUP BY post.ID
                HAVING COUNT(termta.term_taxonomy_id) = 0";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }

    public static function make_column_as_key_in_db($columns)
    {
        $result = array();
        foreach ($columns as $key => $value) {
            foreach (Fnt_ProductListCons::$column_name_in_db as $column_name => $column_name_in_db) {
                if ($key == $column_name) {
                    $result[$column_name_in_db] = $value;
                }
            }
        }
        return $result;
    }

    public static function make_single_row_to_add_new_product($array)
    {
        $result = array();
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    if (isset($value[0]) && !empty($value[0])) {
                        foreach ($value[0] as $item_key => $item_value) {
                            $value[0][$item_key] = '%' . $key . '%';
                        }
                        $result[$key] = array($value[0]);
                    }
                } else {
                    foreach (Fnt_ProductListCons::$column_name_in_db as $column_name => $column_name_in_db) {
                        if ($key == $column_name_in_db) {
                            $result[$key] = "%" . $column_name . "%";
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function save_settings_data_handler($setting_data = array())
    {
        if (empty($setting_data) || !isset($setting_data['settingsData']) || empty($setting_data['settingsData'])) {
            return $this->response_handler(false, array('message' => 'doing delete'));
        }
        $saved_setting_data = $this->formatting_array_1($setting_data['settingsData']);
        $isExisted = get_option('fnt-settings-data', false);
        if (!$isExisted) {
            add_option('fnt-settings-data', $saved_setting_data);
        } else {
            update_option('fnt-settings-data', $saved_setting_data);
        }
        return $this->response_handler(true, array('message' => 'Save setting data have done successfully !'));
    }

    /**
     * This function will format this array below :
     * array(
     *   array(
     *      name:'key1',
     *      value: 'value1'
     *   ),
     *   array(
     *      name:'key2',
     *      value: 'value2'
     *   )
     * )
     * becomes array with single dimension as below :
     * array(
     *    'key1' => 'value1',
     *    'key2' => 'value2'
     * )
     * empty => return array();
     *
     * @param $setting_data
     * @return array
     **/
    protected function formatting_array_1($setting_data)
    {
        $result = array();
        if (empty($setting_data)) return $result;
        foreach ($setting_data as $item) {
            $result[$item['name']] = $item['value'];
        }
        return $result;
    }

    protected function response_handler($status = TRUE, $data = array())
    {
        return json_encode(array('result' => $status ? "SUCCESS" : "FAILED", 'data' => $data));
    }

    public static function get_weight_unit()
    {
        return get_option('woocommerce_weight_unit', 'Unknown');
    }

    public static function get_dimension_unit()
    {
        return get_option('woocommerce_dimension_unit', 'Unknown');
    }

    public static function get_just_add_color()
    {
        return self::get_plugin_setting_value_by_key('addingRowColor',null);
    }

    public static function extractPassedData($encodePassedData = "")
    {
        if (empty($encodePassedData)) return array();
        $extractFormatData = urldecode($encodePassedData);
        $extractFormatData = str_replace('\"', '\'', $extractFormatData);
        $extractFormatData = stripslashes($extractFormatData);
        $result = json_decode($extractFormatData, true);
        return $result;
    }

    // check attribute exists?
    public static function attribute_name_exists($attribute_name)
    {
        global $wpdb;
        $wpdb->escape_by_ref($attribute_name);
        $name = str_replace('pa_', '', sanitize_title($attribute_name));
        $result = $wpdb->get_var($wpdb->prepare("SELECT attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $name));
        return $result == null ? false : true;
    }

    /*
    get all attribute with format
    array(
        attribute_name => attribute_label
    )
 */
    public static function get_product_attributes()
    {
        $product_attributes = array();
        $attributes = wc_get_attribute_taxonomies();
        foreach ($attributes as $attribute) {
            $product_attributes[$attribute->attribute_name] = $attribute->attribute_label;
        }
        return $product_attributes;
    }

    public static function get_dropdown_list_attributes()
    {
        $result = '';
        $list_parent = Fnt_Core::get_product_attributes();
        foreach ($list_parent as $key_parent => $value_parent) {
            $list_child = get_terms(wc_attribute_taxonomy_name($key_parent), array('hide_empty' => 0));
            if (!empty($list_child)) {
                $result .= '<label>' . empty($value_parent) ? $key_parent : $value_parent . '</label>';
                $result .= '<select class="variation-' . $key_parent . '" value="' . $key_parent . '" attribute-name="' . $key_parent . '">';
                $result .= '<option value="0">Select attribute</option>';
                foreach ($list_child as $item) {
                    $result .= '<option value="' . $item->term_id . '">' . $item->name;
                    $result .= '</option>';
                }
                $result .= '</select>';
            }
        }
        return $result;
    }

    public static function get_all_list_attributes()
    {
        $result = array();
        $list_parent = Fnt_Core::get_product_attributes();
        foreach ($list_parent as $key_parent => $value_parent) {
            $list_child = get_terms(wc_attribute_taxonomy_name($key_parent), array('hide_empty' => 0));
            if (!empty($list_child)) {
                $result[$key_parent] = $key_parent;
            }
        }
        return $result;
    }

    /*
 * from array
 * array(
 *    0 => 'color',
 *    1 => 'size'
 * )
 * to list string
 * $string = "'color', 'size'"
 */
    public static function format_array_to_list_string($array)
    {
        $list_string = '';
        foreach ($array as $key => $value) {
            $list_string .= "'$value',";
        }
        return trim($list_string, ',');
    }

    public static function get_product_attributes_by_list_name($list_name)
    {
        global $wpdb;
        if (empty($list_name)) {
            return array();
        }
        $attribute_taxonomies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
                                                         WHERE attribute_name IN ($list_name)");

        return is_array($attribute_taxonomies) ? $attribute_taxonomies : array();
    }

    // Get attribute label by attribute name
    public static function get_attribute_label($attribute_name)
    {
        global $wpdb;
        $wpdb->escape_by_ref($attribute_name);
        $sql = "SELECT {$wpdb->prefix}woocommerce_attribute_taxonomies.attribute_name FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
                WHERE {$wpdb->prefix}woocommerce_attribute_taxonomies.attribute_name = '$attribute_name'";
        $result = $wpdb->get_var($sql);
        return $result == null ? '' : $result;
    }

    /**
     * Get woocommerce attribute by attribute taxonomy name
     * @param $name
     * @return array|bool|null|object
     */
    public static function get_attribute_taxonomy($name)
    {
        global $wpdb;

        if (taxonomy_is_product_attribute($name)) {
            $name = wc_sanitize_taxonomy_name(str_replace('pa_', '', $name));

            $attribute_taxonomy = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $name));

            return $attribute_taxonomy;
        }

        return false;
    }

    /**
     * Helper to get value in plugin setting by key
     * @param string $key
     * @param string $default
     * @return mixed|string
     * @createdBy ThienLD
     */
    public static function get_plugin_setting_value_by_key($key = '', $default = ''){
        $option = get_option('fnt-settings-data', array());
        if(empty($option)) return $default;
        if (!is_array($option)) {
            $option = unserialize($option);
        }
        $value = isset($option[$key]) ? $option[$key] : $default;
        return $value;
    }
}