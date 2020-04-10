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
$add_product_data_init = !empty($template_args['add_product_data_init']) ? $template_args['add_product_data_init'] : array();
/**
 * The Modal Window, including sidebar and content area.
 * Add menu items to ".navigation-bar nav ul"
 * Add content to ".backbone_modal-main article"
 */
?>
<script type="text/html" id='tmpl-add-product-backbone-modal-window'>
    <div class="backbone_modal">
        <a class="backbone_modal-close dashicons dashicons-no" href="#"
           title="<?php echo __( 'Close', 'backbone_modal' ); ?>"><span
                class="screen-reader-text"><?php echo __( 'Close', 'backbone_modal' ); ?></span></a>

        <div class="backbone_modal-content">
            <div class="navigation-bar">
                <h2><?php echo __( 'Product Types', 'backbone_modal' ); ?></h2>
                <nav>
                    <ul></ul>
                </nav>
            </div>
			<section class="backbone_modal-main" role="main">
				<article id="tab-simple-product" class="tab-content-block" data-product-tab-type="simple">
                    <h2><?php echo __( 'Simple Products', 'backbone_modal' ); ?></h2>
                    <div class="list-product-items"></div>
                </article>
				<article id="tab-grouped-product" class="tab-content-block" data-product-tab-type="grouped">
                    <h2><?php echo __( 'Grouped Product', 'backbone_modal' ); ?></h2>
                    <div class="list-grouped-product-items-tag">

                    </div>
                </article>
				<article id="tab-external-product" class="tab-content-block" data-product-tab-type="external">
                    <h2><?php echo __( 'External/Affiliate Product', 'backbone_modal' ); ?></h2>
                    <div class="list-external-product-items-tag">

                    </div>
                </article>
				<article id="tab-variable-product" class="tab-content-block" data-product-tab-type="variable">
                    <h2><?php echo __( 'Variable Product', 'backbone_modal' ); ?></h2>
                    <div class="list-variable-product-items-tag">

                    </div>
                </article>
				<footer>
					<div class="inner text-right">
						<button id="btn-cancel"
						        class="button button-large"><?php echo __( 'Cancel', 'backbone_modal' ); ?></button>
						<button id="btn-ok"
						        class="button button-primary button-large"><?php echo __( 'Save &amp; Continue', 'backbone_modal' ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
</script>

<?php
/**
 * The Modal Backdrop
 */
?>
<script type="text/html" id='tmpl-add-product-backbone-modal-backdrop'>
    <div class="backbone_modal-backdrop">&nbsp;</div>
</script>
<?php
/**
 * Base template for a navigation-bar menu item ( and the only *real* template in the file ).
 */
?>
<script type="text/html" id='tmpl-add-product-backbone-modal-menu-item'>
    <li class="nav-item"><a href="{{ data.url }}">{{ data.name }}</a></li>
</script>
<?php
/**
 * A menu item separator.
 */
?>
<script type="text/html" id='tmpl-add-product-backbone-modal-menu-item-separator'>
    <li class="separator">&nbsp;</li>
</script>

<!-- Simple product-->
<script type="text/html" id='tmpl-add-product-backbone-modal-add-simple-product-item'>
    <div class="bootstrap-wrapper list-simple-product-items wrapper-list-product-items">
        <ul class="nav nav-pills tab-list-simple-product">
            <li class="active">
                <a data-toggle="pill" href="#" class="add-more-simple-product" >
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </a>
            </li>
        </ul>
        <div class="tab-content tab-content-simple-product">

        </div>
    </div>
</script>

<script type="text/html" id='tmpl-tab-content-simple-product'>
    <div id="{{ data.tabID }}" class="tab-pane fade">
        <form id="{{ data.formID }}" class="form-horizontal"></form>
    </div>
</script>

<script type="text/html" id='tmpl-tab-list-simple-product'>
    <li class="li-product-item">
        <span class="glyphicon glyphicon-remove-circle remove-tab-product hidden" form_id = "{{data.formID}}" style=""></span>
        <a data-toggle="pill" href="#{{ data.tabID }}">{{ data.tabTitle }}</a>
    </li>
</script>

<!-- Simple product-->

<!-- Grouped product-->
<script type="text/html" id='tmpl-add-product-backbone-modal-add-grouped-product-item'>
    <div class="bootstrap-wrapper list-grouped-product-items wrapper-list-grouped-product-items">
        <ul class="nav nav-pills tab-list-grouped-product">
            <li class="active"><a data-toggle="pill" href="#" class="add-more-grouped-product" ><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a></li>
        </ul>
        <div class="tab-content tab-content-grouped-product">

        </div>
    </div>
</script>

<script type="text/html" id='tmpl-tab-content-grouped-product'>
    <div id="{{ data.tabID }}" class="tab-pane fade">
        <form id="{{ data.formID }}" class="form-horizontal"></form>
    </div>
</script>

<script type="text/html" id='tmpl-tab-list-grouped-product'>
    <li class="li-product-item">
        <span class="glyphicon glyphicon-remove-circle remove-tab-product hidden" form_id = "{{data.formID}}" style=""></span>
        <a data-toggle="pill" href="#{{ data.tabID }}">{{ data.tabTitle }}</a>
    </li>
</script>


<script type="text/html" id='tmpl-product-row-item'>
    <tr class="add-new">
        {{data.htmlAppend}}
    </tr>
    <tr class="hidden"></tr>
    <tr class="row-edit-excerpt hidden" id="text-area-{{data.id}}" value="{{data.id}}">
        <td colspan="24">
            <span>Text area</span>
        </td>
    </tr>
</script>

<!--External Product-->
<script type="text/html" id='tmpl-add-product-backbone-modal-add-external-product-item'>
    <div class="bootstrap-wrapper list-external-product-items wrapper-list-external-product-items">
        <ul class="nav nav-pills tab-list-external-product">
            <li class="active">
                <a data-toggle="pill" href="#" class="add-more-external-product" >
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </a>
            </li>
        </ul>
        <div class="tab-content tab-content-external-product">

        </div>
    </div>
</script>

<script type="text/html" id='tmpl-tab-content-external-product'>
    <div id="{{ data.tabID }}" class="tab-pane fade">
        <form id="{{ data.formID }}" class="form-horizontal"></form>
    </div>
</script>

<script type="text/html" id='tmpl-tab-list-external-product'>
    <li class="li-product-item">
        <span class="glyphicon glyphicon-remove-sign remove-tab-product hidden" form_id = "{{data.formID}}" style=""></span>
        <a data-toggle="pill" href="#{{ data.tabID }}">{{ data.tabTitle }}</a>
    </li>
</script>
<!--External product-->

<!--Variable Product-->
<script type="text/html" id='tmpl-add-product-backbone-modal-add-variable-product-item'>
    <div class="bootstrap-wrapper list-variable-product-items wrapper-list-variable-product-items">
        <ul class="nav nav-pills tab-list-variable-product">
            <li class="active"><a data-toggle="pill" href="#" class="add-more-variable-product" ><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a></li>
        </ul>
        <div class="tab-content tab-content-variable-product">

        </div>
    </div>
</script>

<script type="text/html" id='tmpl-tab-content-variable-product'>
    <div id="{{ data.tabID }}" class="tab-pane variable fade ">
        <ul class="nav nav-pills tab-list-variable-product-content">
            <li class="active form-variable-product"><a data-toggle="pill" href="#" class="variable-product" >Variable</a></li>
            <li class="active form-variation-product"><a data-toggle="pill" href="#" class="variation-product" >Variation</a></li>
        </ul>
        <div class="wrap-variable">
            <form id="{{ data.formID }}" class="form-horizontal"></form>
        </div>

        <div class="wrap-variation form-group hidden">
            <div class="form-group wrap-select-attribute">
            </div>
            <button class="form-group button button-primary button-large btn-add-variation">Add Variation</button>
            <br>
            <br>
            <div class="form-group wrap-fields-variation" list-variations-selected="">

            </div>


        </div>
    </div>


</script>

<script type="text/html" id="tmpl-attribute-for-variation">
    <div class="single-attribute">
        <table>
            <tbody>
                <tr>
                    <td>
                        <label>Name:</label>
                        <strong>{{data.name}}</strong>
                    </td>

                </tr>
                <tr></tr>
                <tr></tr>
            </tbody>
        </table>
        <label>Visible on the product page</label>
        <input class="variation-attribute-visible-on-product-page" type="checkbox"/>
        <label>Used for variations</label>
        <input class="variation-attribute-used-for-variations"/>

    </div>
</script>

<script type="text/html" id='tmpl-tab-list-variable-product'>
    <li class="li-product-item">
        <span class="glyphicon glyphicon-remove-circle remove-tab-product hidden" form_id = "{{data.formID}}" style=""></span>
        <a data-toggle="pill" href="#{{ data.tabID }}">{{ data.tabTitle }}</a>
    </li>
</script>

<script type="text/html" id="tmpl-variation-content">
        <input class="selected-attribute-name hidden" />
        <div class="wrap-variation-image">
            <div class="wrap-variation-plus">
                <span class="wrap-gallery-image wrapper-gallery-plus bootstrap-wrapper variation-wrapper-gallery-plus">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </span>
            </div>
            <div class="wrap-variation-featured-image hidden">
                <span class="variation-featured-image bootstrap-wrapper">
                    <span class="glyphicon glyphicon-remove-circle variation-featured-image-hover" style=""></span>
                     <img src="" width="40px" height="40px" class="image-thumb-gallery"/>
                </span>
            </div>
            <input type="hidden" name="upload_image_id[0]" class="upload_image_id" value="340">
        </div>

        <label> SKU:</label>
        <input class="variation-sku" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_SKU] ?>" type="text" />

        <label>Enabled</label>
        <input class="variation-enabled" type="checkbox" />

        <label>Downloadable</label>
        <input class="variation-downloadable" type="checkbox" />

        <label>Virtual</label>
        <input class="variation-virtual" type="checkbox" />

        <label>Regular Price</label>
        <input class="variation-regular-price" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_REGULAR_PRICE] ?>" type="text" />

        <label>Sale Price</label>
        <input class="variation-sale-price" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_SALE_PRICE] ?>" type="text" />

        <label>Stock Qty</label>
        <input class="variation-stock-quantity" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_STOCK_QUANTITY] ?>" type="text" />

        <label>Allow Backorders?</label>
        <select class="variation-backorder" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_BACK_ORDERS] ?>">
            <option value="no">Do not allow</option>
            <option value="notify">Allow, but notify customer</option>
            <option value="yes">Allow</option>
        </select>

        <label>Stock status</label>
        <select class="variation-stock-status" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_STOCK] ?>">
            <option value="instock">In stock</option>
            <option value="outofstock">Out of stock</option>
        </select>

        <label>Weight (<?php echo Fnt_Core::get_weight_unit();?>)</label>
        <input type="text" class="variation-weight" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_WEIGHT] ?>" />

        <label>Dimensions (LxWxH) (<?php echo Fnt_Core::get_dimension_unit();?>)</label>
        <input type="text" class="variation-length" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_LENGTH] ?>" />
        <input type="text" class="variation-width" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_WIDTH] ?>" />
        <input type="text" class="variation-height" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_HEIGHT] ?>" />

        <label>Shipping class</label>
        <select class="variation-shipping">
            <option>Same as parent</option>
        </select>

        <label>Variation Description</label>
        <textarea class="variation-description" key = "<?php echo Fnt_ProductListCons::$column_name_in_db[Fnt_ProductListCons::COLUMN_VARIATION_DESCRIPTION] ?>"></textarea>

</script>
<!--Variable Product-->


<script type="text/html" id='tmpl-external-row-item'>
    <tr class="add-new">
        {{data.htmlAppend}}
    </tr>
    <tr class="hidden"></tr>
    <tr class="row-edit-excerpt hidden" id="text-area-{{data.id}}" value="{{data.id}}">
        <td colspan="24">
            <span>Text area</span>
        </td>
    </tr>
</script>