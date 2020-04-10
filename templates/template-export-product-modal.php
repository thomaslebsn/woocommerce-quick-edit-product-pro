<?php
/**
 * Backbone Templates
 * This file contains all of the HTML used in our modal and the workflow itself.
 *
 * Each template is wrapped in a script block ( note the type is set to "text/html" ) and given an ID prefixed with
 * 'tmpl'. The wp.template method retrieves the contents of the script block and converts these blocks into compiled
 * templates to be used and reused in your application.
 */

$page_hook_name = !empty($template_args['page_hook_name']) ? $template_args['page_hook_name'] : "";
$export_obj = !empty($template_args['export_obj'])? $template_args['export_obj'] : null;
/**
 * The Modal Window, including sidebar and content area.
 * Add menu items to ".navigation-bar nav ul"
 * Add content to ".backbone_modal-main article"
 */
?>
<script type="text/html" id='tmpl-export-product-backbone-modal-window'>
    <div class="backbone_modal">
        <a class="backbone_modal-close dashicons dashicons-no" href="#" title="<?php echo __( 'Close', 'backbone_modal' ); ?>">
            <span class="screen-reader-text">
                <?php echo __( 'Close', 'backbone_modal' ); ?>
            </span>
        </a>
        <div class="backbone_modal-content">
            <section class="backbone_modal-main custom-backbone-modal-main" role="main">
                <header><h1><?php echo __( 'Export', 'backbone_modal' ); ?></h1></header>
                <article id="export-area" class="tab-content-block">
                    <div class="main-export-area export-columns-setting" id="export-columns-setting">
                        <div class="bootstrap-wrapper">
                            <form id="form-export-product" class="form-horizontal">

                            </form>
                        </div>
                    </div>
                </article>
                <footer>
                    <div class="inner text-right">
                        <button id="btn-cancel-export" class="button button-large"><?php echo __( 'Cancel', 'backbone_modal' ); ?></button>
                    </div>
                </footer>
            </section>
        </div>
    </div>
</script>
