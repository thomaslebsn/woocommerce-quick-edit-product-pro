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
$form_import_products_url = !empty($template_args['form_import_products_url']) ? $template_args['form_import_products_url'] : "";
/**
 * The Modal Window, including sidebar and content area.
 * Add menu items to ".navigation-bar nav ul"
 * Add content to ".backbone_modal-main article"
 */
?>
<script type="text/html" id='tmpl-import-product-backbone-modal-window'>
	<div class="backbone_modal">
		<a class="backbone_modal-close dashicons dashicons-no" href="#" title="<?php echo __( 'Close', 'backbone_modal' ); ?>">
            <span class="screen-reader-text">
                <?php echo __( 'Close', 'backbone_modal' ); ?>
            </span>
        </a>
		<div class="backbone_modal-content">
			<div class="navigation-bar">
				<nav>
					<ul></ul>
				</nav>
			</div>
			<section class="backbone_modal-main" role="main">
				<article id="tab-create-template-product" class="tab-content-block">
                    <h2><?php echo __( 'Create template', 'backbone_modal' ); ?></h2>
                    <div class="create-template-area">
                        <div class="bootstrap-wrapper">
                            <form id="form-create-excel-template-import" class="form-horizontal">
                            </form>
                        </div>
                    </div>
                </article>
                <article id="tab-import-product" class="tab-content-block">
                    <h2><?php echo __( 'Import', 'backbone_modal' ); ?></h2>
                    <div class="importing-product-area">
                        <div id="import_products">
                            <div class="bootstrap-wrapper">
                                <form id="form-import-products"
                                      action="<?php echo $form_import_products_url; ?>"
                                      method="post"
                                      enctype="multipart/form-data"
                                      name="form-import-products"
                                      class="form-horizontal">
                                    <div class="col-sm-8">
                                        <input type="file" id="input-file-importing" name="input-file-importing" data-filename-placement="inside" title="Choose a file for importing ...">
                                    </div>
                                    <div class="col-sm-4">
                                        <button type="submit" name="btn-import-product" id="btn-import-product" class="btn button button-primary button-large">Import</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
				<footer>
					<div class="inner text-right">
						<button id="btn-cancel-import" class="button button-large"><?php echo __( 'Cancel', 'backbone_modal' ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
</script>
