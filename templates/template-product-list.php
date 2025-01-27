<?php
$products_obj = !empty($template_args['products_obj']) ? $template_args['products_obj'] : null;
$message = !empty($template_args['messages']) ? $template_args['messages'] : "";
$page_hook_name = !empty($template_args['page_hook_name']) ? $template_args['page_hook_name'] : "";
$add_products_modal_box = !empty($template_args['add_products_modal_box']) ? $template_args['add_products_modal_box'] : "";
$add_products_backbone_modal_box = !empty($template_args['add_products_backbone_modal_box']) ? $template_args['add_products_backbone_modal_box'] : "";
$settings_backbone_modal_box = !empty($template_args['settings_backbone_modal_box']) ? $template_args['settings_backbone_modal_box'] : "";
$wp_editor_backbone_modal_box = !empty($template_args['wp_editor_backbone_modal_box']) ? $template_args['wp_editor_backbone_modal_box'] : "";
$add_product_data_init = !empty($template_args['add_product_data_init']) ? $template_args['add_product_data_init'] : array();
$qs_page = !empty($template_args['qs_page']) ? $template_args['qs_page'] : "";
$post_status = !empty($template_args['post_status']) ? $template_args['post_status'] : "";
$qs_post_type = !empty($template_args['qs_post_type']) ? $template_args['qs_post_type'] : "";
?>
<div class="wrap">
    <h2>
        <?php
        $title = "WooCommerce Bulk Edit Products Premium";
        echo $title;
        ?>
    </h2>
    <?php
    $search_label = Fnt_Url_Handler::get_search_result_label();
    if (!empty($products_obj) && !empty($search_label)) :
        ?>
        <div class="bootstrap-wrapper">
            <div class="alert alert-success fade in">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <?php echo __("<strong>" . $search_label . "</strong>", 'fnt'); ?>
            </div>
        </div>
    <?php
    endif;
    $flag = 1;
    $save_status = get_option('fnt-save-all-data-successfully', '');
    if (!empty($save_status)) {
        $flag = $save_status;
        delete_option('fnt-save-all-data-successfully');
        ?>
        <div class="bootstrap-wrapper">
            <div class="alert alert-success fade in">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <strong>
                    <?php
                    if ($flag === 1) {
                        echo __('Save All Products Successfully!', 'fnt');
                    } else {
                        echo __('Save Products Successfully!', 'fnt');
                    }
                    ?>
                </strong>
            </div>
        </div>
    <?php } ?>
    <input type="hidden" name="page-hook-name" id="page-hook-name" value="<?php echo $page_hook_name; ?>"/>
    <input type="hidden" name="page" value="<?php echo $qs_page; ?>"/>
    <input type="hidden" name="admin-url" id="admin-url" value="<?php echo admin_url('/'); ?>"/>
    <div id="table-header" class="block-cta">
        <a href="#" class="button button-primary button-large save-all fnt-button-cta" disabled="disabled"
           id="button-save-all-product-data"><?php echo __('Save All', 'fnt'); ?></a>
        <?php
        $admin_url = admin_url();
        $add_new_product_link = $admin_url . 'post-new.php?post_type=product&amp;fnt_iframe_popup=1';
        ?>
        <a href="#" title="Add new product" link="<?php echo $add_new_product_link; ?>" disabled="disabled"
           class="button button-primary button-large add-product-backbone-modal fnt-button-cta"><?php echo __('Add(s)', 'fnt'); ?></a>
        <a href="#" class="button button-primary button-large settings-backbone-modal fnt-button-cta"
           disabled="disabled"><?php echo __('Settings', 'fnt'); ?></a>
    </div>
    <?php if (!empty($message)) :
        $classes = "alert-danger";
        ?>
        <div class="bootstrap-wrapper">
            <div class="alert <?php echo $classes; ?> fade in">
                <a href="#" class="close" data-dismiss="alert">&times;</a>
                <?php echo __(implode("<br/>", $message), 'fnt'); ?>
            </div>
        </div>
    <?php endif; ?>

    <form id="posts-filter" method="get" action="?post_type=product&page=bulk-edit-products-premium-submenu-page">
        <input type="hidden" name="post_type" value="<?php echo $qs_post_type; ?>"/>
        <input type="hidden" name="page" value="<?php echo $qs_page; ?>"/>
        <input type="hidden" name="post_status" value="<?php echo $post_status; ?>"/>
        <input type="hidden" name="product-form-submit" value="1"/>
        <?php
        if (!empty($products_obj)) {
            $products_obj->prepare_items();
            $products_obj->search_box(__('Search Products', 'fnt'), 'product');
            $products_obj->display();
        }
        ?>
    </form>
    <!-- Use to show popup, don't remove this if not permission -->
    <div class="bootstrap-wrapper" id="popup-message">
    </div>
    <!-- End HTML show popup -->
    <!-- HTML to make scroll at bottom for product table -->
    <div class="table-view-bottom-scroll hidden">
        <div class="scroll-div">
        </div>
    </div>
    <!-- End HTML to make scroll -->
    <?php
    // product categories selection
    echo Fnt_CustomProductList::get_categories_selection();
    ?>
</div>

<?php
// since ver 1.1, change flow of way get data to use in client side(javascript)
if (!empty($products_obj) && !empty($products_obj->items)) {
    $product_items_full = $products_obj->get_full_product_data();
    $product_attributes = $products_obj->get_full_product_attributes();
} else {
    $product_items_full = array();
    $product_attributes = array();
}

wp_localize_script('fnt_product_list_handler', 'fnt_product_data', $product_items_full);
wp_localize_script('fnt_product_list_handler', 'fnt_product_attributes', $product_attributes);

?>

<?php if (!empty($add_products_modal_box)) : ?>
    <?php echo $add_products_modal_box; ?>
<?php endif; ?>

<?php if (!empty($add_products_backbone_modal_box)) : ?>
    <?php echo $add_products_backbone_modal_box; ?>
<?php endif; ?>

<?php if (!empty($settings_backbone_modal_box)) : ?>
    <?php echo $settings_backbone_modal_box; ?>
<?php endif; ?>

<?php if (!empty($wp_editor_backbone_modal_box)) : ?>
    <?php echo $wp_editor_backbone_modal_box; ?>
<?php endif; ?>

<script type="text/html" id='tmpl-gallery-default-html'>
    <span class="wrap-gallery-image bootstrap-wrapper wrapper-gallery-plus">
        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
    </span>
</script>

<script type="text/html" id='tmpl-remove-image-default-html'>
    <span class="wrap-gallery-image bootstrap-wrapper wrapper-gallery-plus">
        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
    </span>
</script>

<script type="text/html" id='tmpl-image-default-html'>
    <span class="%class_hover% bootstrap-wrapper">
        <span class="glyphicon glyphicon-remove-circle %class_wrap%" data-attachment-id="%attachment_id%"
              style=""></span>
        <img src="%thumb_url%" width="40px" height="40px" class="image-thumb-gallery"/>
    </span>
</script>

<script type="text/html" id='tmpl-image-add-new-html'>
    <img src="%thumb_url%" width="40px" height="40px" class="image-thumb-gallery"/>
</script>
