<?php
    if ( ! defined( 'LOADED_PRODUCTS_PER_PAGE' ) ) {
        define( "LOADED_PRODUCTS_PER_PAGE", 10 );
    }
    if ( ! defined( 'FNT_POST_TYPE' ) ) {
        define( "FNT_POST_TYPE", 'product' );
    }
    if ( ! defined( 'OPTION_NONE_VALUE' ) ) {
        define( "OPTION_NONE_VALUE", -1 );
    }
    if ( ! defined( 'FNT_IS_IMPORTING_META_KEY' ) ) {
        define( "FNT_IS_IMPORTING_META_KEY", "fnt_is_importing" );
    }



    //Class full name: Fnt_ProductListConstantDefinition
    class Fnt_ProductListCons {

        const COLUMN_GALLERY = 'product_gallery';
        const COLUMN_THUMBNAIL = 'thumb';
        const COLUMN_TYPE = 'product_type';

        const COLUMN_ID = 'id';
        const COLUMN_NAME = 'post_title';
        const COLUMN_SLUG = 'post_name';
        const COLUMN_DATE = 'date';
        const COLUMN_PASSWORD = 'password';
        const COLUMN_PRIVATE = 'private';
        const COLUMN_SKU = 'sku';
        const COLUMN_REGULAR_PRICE = 'regular_price';
        const COLUMN_SALE_PRICE = 'sale_price';
        const COLUMN_TAX_STATUS = 'tax_status';
        const COLUMN_TAX_CLASS = 'tax_class';
        const COLUMN_WEIGHT = 'weight';
        const COLUMN_LENGTH = 'length';
        const COLUMN_WIDTH = 'width';
        const COLUMN_HEIGHT = 'height';

        const COLUMN_VISIBILITY = 'visibility';
        const COLUMN_FEATURE = 'featured';
        const COLUMN_STOCK = 'stock_status';
        const COLUMN_STOCK_QUANTITY = 'stock';
        const COLUMN_BACK_ORDERS = 'back_orders';
        const COLUMN_ORDER = 'menu_order';
        const COLUMN_TAG = 'product_tag';
        const COLUMN_ALLOW_COMMENTS = 'comment_status';//Enable reviews in woocommerce
        const COLUMN_STATUS = 'status';
        const COLUMN_CATEGORIES = 'product_cat';

        const COLUMN_CONTENT = 'product_content';
        const COLUMN_EXCERPT = 'product_excerpt';

        const COLUMN_SOLD_INDIVIDUALLY = 'sold_individually';
        const COLUMN_SHIPPING_CLASS = 'product_shipping_class';
        const COLUMN_PURCHASE_NOTE = 'purchase_note';
        const COLUMN_MENU_ORDER = 'menu_oder';
        const COLUMN_MODIFYING_STATUS = 'modifying_product';
        const COLUMN_AUTHOR = 'post_author';

        const COLUMN_RELATIONSHIP = 'variable_relationship'; // for export excel template of product variable
        const COLUMN_VARIATION_DESCRIPTION = "variation_description";
        const COLUMN_ATTRIBUTE = "product_attributes";

        // @demo: add new columns manage stock
        const COLUMN_MANAGE_STOCK = 'manage_stock';

        // Columns default for all product type
        public static $column_defaults = array(
            self::COLUMN_NAME => self::COLUMN_NAME,
            self::COLUMN_CONTENT => self::COLUMN_CONTENT,
            self::COLUMN_EXCERPT => self::COLUMN_EXCERPT,
            self::COLUMN_CONTENT => self::COLUMN_CONTENT,
            self::COLUMN_CATEGORIES => self::COLUMN_CATEGORIES,
            self::COLUMN_TAG => self::COLUMN_TAG,
            self::COLUMN_FEATURE => self::COLUMN_FEATURE,
            self::COLUMN_STATUS => self::COLUMN_STATUS,
            self::COLUMN_ALLOW_COMMENTS => self::COLUMN_ALLOW_COMMENTS,
            self::COLUMN_MENU_ORDER => self::COLUMN_MENU_ORDER
        );
        //
        public static $column_product_type = array(
            'simple' => array(
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_REGULAR_PRICE => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE => self::COLUMN_SALE_PRICE,
                self::COLUMN_MANAGE_STOCK => self::COLUMN_MANAGE_STOCK,
                self::COLUMN_STOCK_QUANTITY => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_STOCK => self::COLUMN_STOCK,
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_SOLD_INDIVIDUALLY => self::COLUMN_SOLD_INDIVIDUALLY,
                self::COLUMN_WEIGHT => self::COLUMN_WEIGHT,
                self::COLUMN_LENGTH => self::COLUMN_LENGTH,
                self::COLUMN_WIDTH => self::COLUMN_WIDTH,
                self::COLUMN_HEIGHT => self::COLUMN_HEIGHT,
                self::COLUMN_SHIPPING_CLASS => self::COLUMN_SHIPPING_CLASS,
                self::COLUMN_PURCHASE_NOTE => self::COLUMN_PURCHASE_NOTE
            ),
            'grouped' => array(
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_STOCK_QUANTITY => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_STOCK => self::COLUMN_STOCK,
                self::COLUMN_PURCHASE_NOTE => self::COLUMN_PURCHASE_NOTE
            ),
            'external' => array(
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_REGULAR_PRICE => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE => self::COLUMN_SALE_PRICE,
                self::COLUMN_ALLOW_COMMENTS => self::COLUMN_ALLOW_COMMENTS
            ),
            'variable' => array(
                self::COLUMN_MANAGE_STOCK => self::COLUMN_MANAGE_STOCK,
                self::COLUMN_STOCK_QUANTITY => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_SOLD_INDIVIDUALLY => self::COLUMN_SOLD_INDIVIDUALLY,
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_LENGTH => self::COLUMN_LENGTH,
                self::COLUMN_WIDTH => self::COLUMN_WIDTH,
                self::COLUMN_HEIGHT => self::COLUMN_HEIGHT,
                self::COLUMN_WEIGHT => self::COLUMN_WEIGHT,
                self::COLUMN_SHIPPING_CLASS => self::COLUMN_SHIPPING_CLASS,
                self::COLUMN_PURCHASE_NOTE => self::COLUMN_PURCHASE_NOTE
            ),
            'variation' => array(
                self::COLUMN_THUMBNAIL => self::COLUMN_THUMBNAIL,
                self::COLUMN_ATTRIBUTE => self::COLUMN_ATTRIBUTE,
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_MANAGE_STOCK => self::COLUMN_MANAGE_STOCK,
                self::COLUMN_STOCK => self::COLUMN_STOCK,
                self::COLUMN_STOCK_QUANTITY => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_REGULAR_PRICE => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE=> self::COLUMN_SALE_PRICE,
                self::COLUMN_WEIGHT => self::COLUMN_WEIGHT,
                self::COLUMN_LENGTH => self::COLUMN_LENGTH,
                self::COLUMN_WIDTH => self::COLUMN_WIDTH,
                self::COLUMN_HEIGHT => self::COLUMN_HEIGHT,
                self::COLUMN_TYPE => self::COLUMN_TYPE,
                self::COLUMN_VARIATION_DESCRIPTION => self::COLUMN_VARIATION_DESCRIPTION
            ),
            '' => array()
        );

        /* Note: This list must be sync with columns in table @ntn */
        public static $product_mapping_js = array(
            'default' => array(
                self::COLUMN_ID => self::COLUMN_ID,
                self::COLUMN_NAME => self::COLUMN_NAME,
                self::COLUMN_CONTENT => self::COLUMN_CONTENT,
                self::COLUMN_EXCERPT => self::COLUMN_EXCERPT,
                self::COLUMN_CATEGORIES => self::COLUMN_CATEGORIES,
                self::COLUMN_TAG => self::COLUMN_TAG,
                self::COLUMN_THUMBNAIL => self::COLUMN_THUMBNAIL,
                self::COLUMN_GALLERY => self::COLUMN_GALLERY,
                self::COLUMN_FEATURE => self::COLUMN_FEATURE,
                self::COLUMN_STATUS => self::COLUMN_STATUS
            ),
            'simple' => array(
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_REGULAR_PRICE => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE => self::COLUMN_SALE_PRICE,
                self::COLUMN_MANAGE_STOCK => self::COLUMN_MANAGE_STOCK,
                self::COLUMN_STOCK_QUANTITY => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_STOCK => self::COLUMN_STOCK,
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_SOLD_INDIVIDUALLY => self::COLUMN_SOLD_INDIVIDUALLY,
                self::COLUMN_WEIGHT => self::COLUMN_WEIGHT,
                self::COLUMN_LENGTH => self::COLUMN_LENGTH,
                self::COLUMN_WIDTH => self::COLUMN_WIDTH,
                self::COLUMN_HEIGHT => self::COLUMN_HEIGHT,
                self::COLUMN_ALLOW_COMMENTS => self::COLUMN_ALLOW_COMMENTS
            ),
            'grouped' => array(
                self::COLUMN_STOCK => self::COLUMN_STOCK,
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_ALLOW_COMMENTS => self::COLUMN_ALLOW_COMMENTS
            ),
            'external' => array(
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_REGULAR_PRICE => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE => self::COLUMN_SALE_PRICE,
                self::COLUMN_ALLOW_COMMENTS => self::COLUMN_ALLOW_COMMENTS
            ),
            'variable' => array(
                self::COLUMN_SKU => self::COLUMN_SKU,
                self::COLUMN_REGULAR_PRICE => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE => self::COLUMN_SALE_PRICE,
                self::COLUMN_STOCK_QUANTITY => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_BACK_ORDERS => self::COLUMN_BACK_ORDERS,
                self::COLUMN_SOLD_INDIVIDUALLY => self::COLUMN_SOLD_INDIVIDUALLY,
                self::COLUMN_LENGTH => self::COLUMN_LENGTH,
                self::COLUMN_WIDTH => self::COLUMN_WIDTH,
                self::COLUMN_HEIGHT => self::COLUMN_HEIGHT,
                self::COLUMN_WEIGHT => self::COLUMN_WEIGHT,
                // @demo: add column manage stock
                self::COLUMN_MANAGE_STOCK => self::COLUMN_MANAGE_STOCK
            ),
            'variation' => array(
                self::COLUMN_ID                    => self::COLUMN_ID,
                self::COLUMN_ATTRIBUTE             => self::COLUMN_ATTRIBUTE,
                self::COLUMN_THUMBNAIL             => self::COLUMN_THUMBNAIL,
                self::COLUMN_SKU                   => self::COLUMN_SKU,
                self::COLUMN_MANAGE_STOCK          => self::COLUMN_MANAGE_STOCK,
                self::COLUMN_STOCK                 => self::COLUMN_STOCK,
                self::COLUMN_STOCK_QUANTITY        => self::COLUMN_STOCK_QUANTITY,
                self::COLUMN_BACK_ORDERS           => self::COLUMN_BACK_ORDERS,
                self::COLUMN_REGULAR_PRICE         => self::COLUMN_REGULAR_PRICE,
                self::COLUMN_SALE_PRICE            => self::COLUMN_SALE_PRICE,
                self::COLUMN_WEIGHT                => self::COLUMN_WEIGHT,
                self::COLUMN_LENGTH                => self::COLUMN_LENGTH,
                self::COLUMN_WIDTH                 => self::COLUMN_WIDTH,
                self::COLUMN_HEIGHT                => self::COLUMN_HEIGHT,
                self::COLUMN_TYPE                  => self::COLUMN_TYPE,
                self::COLUMN_VARIATION_DESCRIPTION => self::COLUMN_VARIATION_DESCRIPTION
            )
        );

        public static $input_numbers = array(
            self::COLUMN_REGULAR_PRICE,
            self::COLUMN_SALE_PRICE,
            self::COLUMN_WEIGHT,
            self::COLUMN_LENGTH,
            self::COLUMN_WIDTH,
            self::COLUMN_HEIGHT
        );

        public static $validate_price = array(
            self::COLUMN_SALE_PRICE,
            self::COLUMN_REGULAR_PRICE
        );


        public static $input_only_numbers = array(
            self::COLUMN_STOCK_QUANTITY
        );

        public static $only_view_columns = array(
            self::COLUMN_TYPE => self::COLUMN_TYPE,
            self::COLUMN_DATE => self::COLUMN_DATE,
            self::COLUMN_SLUG => self::COLUMN_SLUG,
            self::COLUMN_ID => self::COLUMN_ID,
            self::COLUMN_THUMBNAIL => self::COLUMN_THUMBNAIL,
            self::COLUMN_GALLERY => self::COLUMN_GALLERY,
            self::COLUMN_CONTENT => self::COLUMN_CONTENT,
            self::COLUMN_EXCERPT => self::COLUMN_EXCERPT
        );
        public static $column_name_in_db = array(
            self::COLUMN_GALLERY => '_product_image_gallery',
            self::COLUMN_THUMBNAIL => '_thumbnail_id',

            self::COLUMN_ID => 'ID',
            self::COLUMN_NAME => 'post_title',
            self::COLUMN_SLUG => 'post_name',
            self::COLUMN_DATE => 'post_date',
            self::COLUMN_PASSWORD => 'post_password',
            self::COLUMN_PRIVATE => 'post_status',
            self::COLUMN_SKU => '_sku',
            self::COLUMN_REGULAR_PRICE => '_regular_price',
            self::COLUMN_SALE_PRICE => '_sale_price',
            self::COLUMN_TAX_STATUS => '_tax_status',
            self::COLUMN_TAX_CLASS => '_tax_class',
            self::COLUMN_WEIGHT => '_weight',
            self::COLUMN_LENGTH => '_length',
            self::COLUMN_WIDTH => '_width',
            self::COLUMN_HEIGHT => '_height',

            self::COLUMN_VISIBILITY => '_visibility',
            self::COLUMN_FEATURE => '_featured',
            self::COLUMN_STOCK => '_stock_status',
            self::COLUMN_STOCK_QUANTITY => '_stock',
            self::COLUMN_BACK_ORDERS => '_backorders',
            self::COLUMN_ORDER => 'menu_order',
            self::COLUMN_TAG => 'product_tag',
            self::COLUMN_ALLOW_COMMENTS => 'comment_status',
            self::COLUMN_STATUS => 'post_status',
            self::COLUMN_CATEGORIES => 'product_cat',
            self::COLUMN_TYPE => 'product_type',

            self::COLUMN_CONTENT => 'post_content',
            self::COLUMN_EXCERPT => 'post_excerpt',

            self::COLUMN_SOLD_INDIVIDUALLY => '_sold_individually',
            self::COLUMN_SHIPPING_CLASS => 'product_shipping_class',
            self::COLUMN_PURCHASE_NOTE => '_purchase_note',
            self::COLUMN_MENU_ORDER => 'menu_order',
            self::COLUMN_MODIFYING_STATUS => 'modifying_product',
            self::COLUMN_AUTHOR => 'post_author',
            self::COLUMN_VARIATION_DESCRIPTION => '_variation_description',
            self::COLUMN_ATTRIBUTE => '_product_attributes',
            // @demo: add column manage stock
            self::COLUMN_MANAGE_STOCK => '_manage_stock'
        );
        public static $column_format = array(
            self::COLUMN_GALLERY => 'list',
            self::COLUMN_THUMBNAIL => 'int',

            self::COLUMN_ID => 'string',
            self::COLUMN_NAME => 'string',
            self::COLUMN_SLUG => 'string',
            self::COLUMN_DATE => 'datetime',
            self::COLUMN_PASSWORD => 'string',
            self::COLUMN_PRIVATE => 'string',
            self::COLUMN_SKU => 'string',
            self::COLUMN_REGULAR_PRICE => 'float',
            self::COLUMN_SALE_PRICE => 'float',
            self::COLUMN_TAX_STATUS => 'string',
            self::COLUMN_TAX_CLASS => 'string',
            self::COLUMN_WEIGHT => 'float',
            self::COLUMN_LENGTH => 'float',
            self::COLUMN_WIDTH => 'float',
            self::COLUMN_HEIGHT => 'float',

            self::COLUMN_VISIBILITY => 'string',
            self::COLUMN_FEATURE => 'string',
            self::COLUMN_STOCK => 'string',
            self::COLUMN_STOCK_QUANTITY => 'int',
            self::COLUMN_BACK_ORDERS => 'string',
            self::COLUMN_ORDER => 'int',
            self::COLUMN_TAG => 'list',
            self::COLUMN_ALLOW_COMMENTS => 'string',
            self::COLUMN_STATUS => 'string',
            self::COLUMN_CATEGORIES => 'list',
            self::COLUMN_TYPE => 'string',

            self::COLUMN_EXCERPT => 'string',
            self::COLUMN_CONTENT => 'string',

            self::COLUMN_SOLD_INDIVIDUALLY => 'string',
            self::COLUMN_SHIPPING_CLASS => 'list',
            self::COLUMN_PURCHASE_NOTE => 'string',
            self::COLUMN_MENU_ORDER => 'int',
            self::COLUMN_MODIFYING_STATUS => 'int',
            self::COLUMN_AUTHOR => 'string',
            self::COLUMN_RELATIONSHIP => 'list',
            self::COLUMN_VARIATION_DESCRIPTION => 'string',
            self::COLUMN_ATTRIBUTE => 'array',
            // @demo: add column manage stock
            self::COLUMN_MANAGE_STOCK => 'string'
        );
        public static $column_table_in_db = array(
            self::COLUMN_GALLERY => 'postmeta',
            self::COLUMN_THUMBNAIL => 'postmeta',

            self::COLUMN_ID => 'posts',
            self::COLUMN_NAME => 'posts',
            self::COLUMN_SLUG => 'posts',
            self::COLUMN_DATE => 'posts',
            self::COLUMN_PASSWORD => 'posts',
            self::COLUMN_PRIVATE => 'posts',
            self::COLUMN_SKU => 'postmeta',
            self::COLUMN_REGULAR_PRICE => 'postmeta',
            self::COLUMN_SALE_PRICE => 'postmeta',
            self::COLUMN_TAX_STATUS => 'postmeta',
            self::COLUMN_TAX_CLASS => 'postmeta',
            self::COLUMN_WEIGHT => 'postmeta',
            self::COLUMN_LENGTH => 'postmeta',
            self::COLUMN_WIDTH => 'postmeta',
            self::COLUMN_HEIGHT => 'postmeta',

            self::COLUMN_VISIBILITY => 'postmeta',
            self::COLUMN_FEATURE => 'postmeta',
            self::COLUMN_STOCK => 'postmeta',
            self::COLUMN_STOCK_QUANTITY => 'postmeta',
            self::COLUMN_BACK_ORDERS => 'postmeta',
            self::COLUMN_ORDER => 'posts',
            self::COLUMN_TAG => 'terms',
            self::COLUMN_ALLOW_COMMENTS => 'posts',
            self::COLUMN_STATUS => 'posts',
            self::COLUMN_CATEGORIES => 'terms',
            self::COLUMN_TYPE => 'terms',

            self::COLUMN_EXCERPT => 'posts',
            self::COLUMN_CONTENT => 'posts',
            self::COLUMN_SOLD_INDIVIDUALLY => 'postmeta',
            self::COLUMN_SHIPPING_CLASS => 'terms',
            self::COLUMN_PURCHASE_NOTE => 'postmeta',
            self::COLUMN_MENU_ORDER => 'posts',
            self::COLUMN_MODIFYING_STATUS => 'none',
            self::COLUMN_AUTHOR => 'posts',
            self::COLUMN_VARIATION_DESCRIPTION => 'postmeta',
            self::COLUMN_ATTRIBUTE => 'postmeta',
            // @demo: add column manage stock
            self::COLUMN_MANAGE_STOCK => 'postmeta'
        );
        public static $column_table_display = array(
            self::COLUMN_GALLERY => 'Product Gallery',
            self::COLUMN_THUMBNAIL => 'Product Thumbnail',

            self::COLUMN_ID => 'ID',
            self::COLUMN_NAME => 'Product Name',
            self::COLUMN_SLUG => 'Slug',
            self::COLUMN_DATE => 'Date',
            self::COLUMN_PASSWORD => 'Password',
            self::COLUMN_PRIVATE => 'Private',
            self::COLUMN_SKU => 'SKU',
            self::COLUMN_REGULAR_PRICE => 'Regular Price',
            self::COLUMN_SALE_PRICE => 'Sale Price',
            self::COLUMN_TAX_STATUS => 'Tax Status',
            self::COLUMN_TAX_CLASS => 'Tax Class',
            self::COLUMN_WEIGHT => 'Weight',
            self::COLUMN_LENGTH => 'Length',
            self::COLUMN_WIDTH => 'Width',
            self::COLUMN_HEIGHT => 'Height',

            self::COLUMN_VISIBILITY => 'Visibility',
            self::COLUMN_FEATURE => 'Featured',
            self::COLUMN_STOCK => 'Stock',
            self::COLUMN_STOCK_QUANTITY => 'Quantity',
            self::COLUMN_BACK_ORDERS => 'Back Orders',
            self::COLUMN_ORDER => 'Order',
            self::COLUMN_TAG => 'Tags',
            self::COLUMN_ALLOW_COMMENTS => 'Allow Comments',
            self::COLUMN_STATUS => 'Product Status',
            self::COLUMN_CATEGORIES => 'Categories',
            self::COLUMN_TYPE => 'Product Type',

            self::COLUMN_EXCERPT => 'Short Description',
            self::COLUMN_CONTENT => 'Description',
            self::COLUMN_SOLD_INDIVIDUALLY => 'Sold Individually',
            self::COLUMN_SHIPPING_CLASS => 'Shipping Class',
            self::COLUMN_PURCHASE_NOTE => 'Purchase Note',
            self::COLUMN_MENU_ORDER => 'Menu Order',
            self::COLUMN_MODIFYING_STATUS => 'Modifying',
            self::COLUMN_AUTHOR => 'Author',
            self::COLUMN_RELATIONSHIP => 'Relationship',
            self::COLUMN_ATTRIBUTE => 'Attribute',
            self::COLUMN_VARIATION_DESCRIPTION => 'Variation Description',
            // @demo: add column manage stock
            self::COLUMN_MANAGE_STOCK => 'Manage Stock'
        );
        public static $column_mapping = array(
            self::COLUMN_STOCK => array(
                'instock' => 'In stock',
                'outofstock' => 'Out of stock'
            ),
            //yes, no
            self::COLUMN_FEATURE => array(
                'yes' => 'Yes',
                'no' => 'No'
            ),
            //yes, no

            self::COLUMN_PRIVATE => array(
                'private' => 'Yes',
                '' => 'No',
                'publish' => 'No',
                'pending' => 'No',
                'draft' => 'No'
            ),
            //yes, no

            self::COLUMN_TAX_STATUS => array(
                'taxable' => 'Taxable',
                'none' => 'None',
                'shipping' => 'Shipping only'
            ),
            self::COLUMN_TAX_CLASS => array(
                'standard' => 'Standard',
                'reduced-rate' => 'Reduced rate',
                'zero-rate' => 'Zero rate'
            ),
            self::COLUMN_VISIBILITY => array(
                'visible' => 'Catalog & Search',
                'catalog' => 'Catalog',
                'search' => 'Search',
                'hidden' => 'Hidden'
            ),
            self::COLUMN_BACK_ORDERS => array(
                'no' => 'Do not allow',
                'notify' => 'Allow, but notify customer',
                'yes' => 'Allow'
            ),

            self::COLUMN_ALLOW_COMMENTS => array(
                'closed' => 'Closed',
                'open' => 'Open'
            ),
            //yes, no: the not choice value must be at first

            self::COLUMN_SOLD_INDIVIDUALLY => array(
                'no' => 'No',
                'yes' => 'Yes'
            ),
            //yes, no: the not choice value must be at first

            self::COLUMN_STATUS => array(
                'publish' => 'Published',
                'pending' => 'Pending review',
                'draft' => 'Draft',
                'trash' => 'Trash'
            ),
            self::COLUMN_TYPE => array(
                'simple' => 'Simple',
                'grouped' => 'Grouped',
                'variable' => 'Variable',
                'external' => 'External'
            ),
            self::COLUMN_RELATIONSHIP => array(
                'child' => 'Child',
                'parent' => 'Parent'
            ),
            // @demo: add column manage stock
            self::COLUMN_MANAGE_STOCK => array(
                'no' => 'No',
                'yes' => 'Yes'
            )
        );

        public static $product_type_mapping = array(
            self::COLUMN_GALLERY => 'gallery',
            self::COLUMN_THUMBNAIL => 'thumb',
            self::COLUMN_ID => 'input',
            self::COLUMN_NAME => 'input',
            self::COLUMN_SLUG => 'input',
            self::COLUMN_DATE => 'input',
            self::COLUMN_PASSWORD => 'input',
            self::COLUMN_PRIVATE => 'input',
            self::COLUMN_SKU => 'input',
            self::COLUMN_REGULAR_PRICE => 'input',
            self::COLUMN_SALE_PRICE => 'input',
            self::COLUMN_TAX_STATUS => 'input',
            self::COLUMN_TAX_CLASS => 'input',
            self::COLUMN_WEIGHT => 'input',
            self::COLUMN_LENGTH => 'input',
            self::COLUMN_WIDTH => 'input',
            self::COLUMN_HEIGHT => 'input',
            self::COLUMN_VISIBILITY => 'input',
            self::COLUMN_FEATURE => 'checkbox',
            self::COLUMN_STOCK => 'dropdown-list',
            self::COLUMN_STOCK_QUANTITY => 'input',
            self::COLUMN_BACK_ORDERS => 'dropdown-list',
            self::COLUMN_ORDER => 'input',
            self::COLUMN_TAG => 'product-tag',
            self::COLUMN_ALLOW_COMMENTS => 'checkbox',
            self::COLUMN_STATUS => 'dropdown-list',
            self::COLUMN_CATEGORIES => 'product-cat',
            self::COLUMN_TYPE => 'input',
            self::COLUMN_EXCERPT => 'input',
            self::COLUMN_CONTENT => 'input',
            self::COLUMN_SOLD_INDIVIDUALLY => 'checkbox',
            self::COLUMN_SHIPPING_CLASS => 'product-shipping-class',
            self::COLUMN_PURCHASE_NOTE => 'input',
            self::COLUMN_MENU_ORDER => 'input',
            self::COLUMN_MODIFYING_STATUS => 'modifying',
            self::COLUMN_AUTHOR => 'input',
            self::COLUMN_VARIATION_DESCRIPTION => 'input',
            self::COLUMN_ATTRIBUTE => 'array',
            // @demo: add column manage stock
            self::COLUMN_MANAGE_STOCK => 'checkbox'
        );

        public static function get_message_show() {
            $list_message = array(
                'message_warning' => __( 'The changes you made will be lost if you navigate away from this page. Would you like to proceed?', 'fnt' ),
                'message_delete_product' => __( 'All selected product(s) will be DELETED. Would you like to proceed?', 'fnt' ),
                'message_save_all_products' => __( 'All edited product(s) will be SAVED into database. Would you like to proceed?', 'fnt' ),
                'message_save_products' => __( 'Only IMPORTED products on current page will be SAVED into database. Would you like to proceed?', 'fnt' ),
                'message_SKU_exists_global' => __( 'SKU already exists in global list.', 'fnt' ),
                'message_product_name_not_empty' => __( 'Product name is not empty.', 'fnt' ),
                'message_save_product_failed' => __( 'Save failed, please try again!', 'fnt' ),
                'message_SKU_exists' => __( 'SKU already exists.', 'fnt' ),
                'import_product_invalid_file_type' => __( 'Invalid file type. Only accept file with extension .xls or .xlsx !', 'fnt' ),
                'import_product_empty_input_file' => __( 'Import file is required! Please choose your file before importing.', 'fnt' ),
                'delete_product_failed' => __( 'Delete failed, please try again !', 'fnt' ),
                'move_to_trash_product_failed' => __( 'Move to trash failed, please try again!', 'fnt' ),
                'change_product_type_failed' => __( 'Change product type failed, please try again!', 'fnt' ),
                'please_select_product_type_to_change' => __( 'Please select a product type to change to!', 'fnt' ),
                'restore_product_failed' => __( 'Restore failed, please try again!', 'fnt' ),
                'excel_template_setting_successfully' => __( 'Your template is generated successfully!', 'fnt' ),
                'excel_template_setting_some_error' => __( 'Have some errors, please try creating again!', 'fnt' ),
                'export_product_error_selected_columns' => __( 'You must choose at least 3 columns for create template!', 'fnt' ),
                'export_product_have_some_error' => __( 'Have some errors. Please fix them before continuing your process.', 'fnt' ),
                'export_product_successfully' => __( 'Exported successfully!!!', 'fnt' ),
                'export_product_process_have_some_error' => __( 'Have some errors in export processing, please try again!', 'fnt' ),
                'form_setting_modifying_row_color' => __( 'This color use to show row of product(s) is modify by edit inline.', 'fnt' ),
                'form_setting_adding_row_color' => __( 'This color use to show row of product(s) just add by button Add(s).', 'fnt' ),
                'form_setting_set_product_status_on_creating' => __( 'Choose a status for creating a new product. By default status is "Pending".', 'fnt' ),
                'edit_short_description_title' => __( 'Edit short description', 'fnt' ),
                'edit_description_title' => __( 'Edit description', 'fnt' ),
                'edit_product_by_popup_alert' => __( 'Current product is modifying, please save changes before do this action.', 'fnt' ),
                'add_product_by_popup_alert' => __( 'Have products is modifying, please save changes before do this action.', 'fnt' ),
                'variation_modify_alert' => __( 'Current product is have variations modifying, please save changes before do this action.', 'fnt' ),
                'add_product_by_popup_title' => __( 'Add product', 'fnt' ),
                'save_product_error' => __( 'Save failed, please try again!', 'fnt' ),
                'confirm_redirect_page' => __( 'The changes you made will be lost if you navigate away from this page.', 'fnt' ),
                'no_change_detected' => __( 'No change detected!', 'fnt' ),
                'no_items_selected' => __( 'No items selected!', 'fnt' ),
                'no_data_to_export' => __( 'Don\'t have data to export!', 'fnt' ),
                'add_attributes_failed' => __( 'Add attributes failed', 'fnt' ),
                'message_add_new_attribute_term' => __('Enter a name for the new attribute term:', 'fnt'),
                'caution_message_attribute_term_name_empty' => __('Attribute term name is empty. So can\'t add new attribute item.', 'fnt'),
                'caution_when_have_variation_modifying' => __('Have product modifying, data maybe lost. Please save changes!', 'fnt' )
            );

            return $list_message;
        }

        /**
         * Since ver 1.1
         * Use for define columns of variations table
         * @var array
         */
        public static $variation_columns = array(
            self::COLUMN_ID                    => self::COLUMN_ID,
            self::COLUMN_ATTRIBUTE             => self::COLUMN_ATTRIBUTE,
            self::COLUMN_THUMBNAIL             => self::COLUMN_THUMBNAIL,
            self::COLUMN_SKU                   => self::COLUMN_SKU,
            self::COLUMN_MANAGE_STOCK          => self::COLUMN_MANAGE_STOCK,
            self::COLUMN_STOCK                 => self::COLUMN_STOCK,
            self::COLUMN_STOCK_QUANTITY        => self::COLUMN_STOCK_QUANTITY,
            self::COLUMN_BACK_ORDERS           => self::COLUMN_BACK_ORDERS,
            self::COLUMN_REGULAR_PRICE         => self::COLUMN_REGULAR_PRICE,
            self::COLUMN_SALE_PRICE            => self::COLUMN_SALE_PRICE,
            self::COLUMN_WEIGHT                => self::COLUMN_WEIGHT,
            self::COLUMN_LENGTH                => self::COLUMN_LENGTH,
            self::COLUMN_WIDTH                 => self::COLUMN_WIDTH,
            self::COLUMN_HEIGHT                => self::COLUMN_HEIGHT,
            self::COLUMN_TYPE                  => self::COLUMN_TYPE,
            self::COLUMN_VARIATION_DESCRIPTION => self::COLUMN_VARIATION_DESCRIPTION
        );
    }


    class Fnt_ProductAddNewItemInModel {
        const product_empty = -1;
        const product_exists = -2;
        const product_name_empty = -3;
        const product_insert_failed = -4;
        const product_un_error = -5;
        const product_not_exists = -6;
        const exception = -7;
        const product_name_exists = -8;

        public static $product_type = 'product';

        public static $error_code = array(
            self::product_empty => 'Product is empty',
            self::product_exists => 'Product already exists',
            self::product_name_empty => 'Product name is empty',
            self::product_insert_failed => 'Insert failed',
            self::product_un_error => 'Un error',
            self::product_not_exists => 'Product not exists',
            self::exception => 'Error not cover',
            self::product_name_exists => 'Product name already exists'
        );

        public static $product_item_month_mapping = array(
            '1' => '01-Jan',
            '2' => '02-Feb',
            '3' => '03-Mar',
            '4' => '04-Apr',
            '5' => '05-May',
            '6' => '06-Jun',
            '7' => '07-Jul',
            '8' => '08-Aug',
            '9' => '09-Sep',
            '10' => '10-Oct',
            '11' => '11-Nov',
            '12' => '12-Dec'
        );

        public static $product_item_shipping_mapping = array(
            '_no_shipping_class' => 'No shipping class'
        );

        public static $product_item_visibility_mapping = array(
            'visible' => 'Category & Search',
            'category' => 'Category',
            'search' => 'Search',
            'hidden' => 'Hidden'
        );

        public static $product_item_stock_status_mapping = array(
            'instock' => 'In stock',
            'outofstock' => 'Out of stock'
        );

        public static $product_item_backorders_mapping = array(
            'no' => 'Do not allow',
            'notify' => 'Allow, but notify customer',
            'yes' => 'Allow'
        );

        public static $product_item_tax_status_mapping = array(
            'taxable' => 'Taxable',
            'none' => 'None',
            'shipping' => 'Shipping only'
        );

        public static $product_item_tax_class_mapping = array(
            'standard' => 'Standard',
            'reduced-rate' => 'Reduced rate',
            'zero-rate' => 'Zero rate'
        );

        public static $product_item_status_mapping = array(
            'publish' => 'Published',
            'pending' => 'Pending review',
            'draft' => 'Draft'
        );
    }
