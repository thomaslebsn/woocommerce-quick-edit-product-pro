(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.formSimpleProduct = (function(){
        var module = {},
            model = null,
            fields = null,
            form = null,
            formElementID = "#form-add-new-simple-product";
        module.init = function(){
            return module;
        };

        var createModel = function () {
            model = Backbone.Model.extend({
                defaults: {
                    id: 0,
                    title: "",
                    description: "",
                    shortDescription: "",
                    category: "",
                    productTags: "",
                    allowComment: "",
                    productType: "",
                    featureImage: "",
                    galleryImage: "",
                    sku: "",
                    regularPrice: "",
                    salePrice: "",
                    manageStock: "",
                    stockQuantity: "",
                    backOrders: "",
                    stockStatus: "",
                    soldIndividually: "",
                    weight: "",
                    length: "",
                    width: "",
                    height: "",
                    shippingClass: "",
                    purchaseNote: "",
                    enableReviews: ""
                },
                validate: function(attributes, options) {
                    this.errorModel.clear();
                    var title = this.get("title");
                    if(title == null || title == ""){
                        return true;
                    }

                    if (!_.isEmpty(_.compact(this.errorModel.toJSON()))) {
                        return false;
                    }
                }
            });
        };

        var replaceHtmlCategory = function(htmlCategory, name, id, classes){
            htmlCategory = htmlCategory.replace('%name%', name);
            htmlCategory = htmlCategory.replace('%id%', id);
            htmlCategory = htmlCategory.replace('%classes%', classes);
            return htmlCategory;
        };

        var createFields = function () {
            fields = [
                {
                    name: data_product_default.simple_product_fields.post_title, // The key of the model attribute
                    label: "Product Name", // The label to display next to the control
                    control: "input", // This will be converted to InputControl and instantiated from the proper class under the Backform namespace
                    extraClasses: ["post-title","validate[required]"],
                    required:true
                }, {
                    name: data_product_default.simple_product_fields.product_content,
                    label: "Description",
                    control: "textarea",
                    extraClasses: ["custom-rte-style"]
                }, {
                    name: data_product_default.simple_product_fields.product_excerpt,
                    label: "Short Description",
                    control: "textarea",
                    extraClasses: ["custom-rte-style"]
                    //helpMessage: "Be creative!"
                }, {
                    name: data_product_default.simple_product_fields.product_cat,
                    label: "Category",
                    control: "dropdown-list",
                    data: replaceHtmlCategory(data_product_default.category,data_product_default.simple_product_fields.product_cat,'tag-simple-category','simple-category form-control custom-select-style ')
                }, {
                    name: data_product_default.simple_product_fields.product_tag,
                    label: "Product tags",
                    control: "input",
                    extraClasses: ['product-tag-suggest']
                }, {
                    name: "product_type",
                    control: "input-hidden",
                    hiddenValue: "simple"
                }, {
                    name: "feature-image",
                    label: "Feature image",
                    control: "input-image",
                    extraClasses: "product-thumb",
                    columnName: "product_thumb"
                }, {
                    name:  data_product_default.simple_product_fields.thumb,
                    control: "input-hidden",
                    hiddenValue: "",
                    extraClasses: ["thumb-value"]
                }, {
                    name: "gallery-image",
                    label: "Gallery image",
                    control: "input-image",
                    extraClasses: "product-gallery",
                    columnName: "product_gallery",
                    galleryClass: "add-new-gallery"
                }, {
                    name:  data_product_default.simple_product_fields.product_gallery,
                    control: "input-hidden",
                    hiddenValue: "",
                    extraClasses: ["gallery-value"]
                }, {
                    name: data_product_default.simple_product_fields.comment_status,
                    label: "Allow comment",
                    control: "checkbox"
                }, {
                    name: data_product_default.simple_product_fields.featured,
                    label: "Featured",
                    control: "checkbox"
                }, {
                    name: data_product_default.simple_product_fields.sku,
                    label: "SKU",
                    control: "input",
                    extraClasses: ["new-sku"]

                }, {
                    name: data_product_default.simple_product_fields.regular_price,
                    label: "Regular price",
                    control: "input",
                    extraClasses: ["validate[custom[number]]"]
                }, {
                    name: data_product_default.simple_product_fields.sale_price,
                    label: "Sale price",
                    control: "input",
                    extraClasses: ["validate[custom[number]]"]
                }, {
                    name: data_product_default.simple_product_fields.stock,
                    label: "Stock quantity",
                    control: "input",
                    extraClasses: ["custom-field-numeric"]
                }, {
                    name: data_product_default.simple_product_fields.back_orders,
                    label: "Back orders",
                    control: "select",
                    options: [
                        {label: "Do not allow", value: "no"},
                        {label: "Allow, but notify customer", value:"notify"},
                        {label: "Allow", value: "yes"}
                    ]
                }, {
                    name: data_product_default.simple_product_fields.stock_status,
                    label: "Stock status",
                    control: "select",
                    options: [
                        {label: "In stock", value: "instock"},
                        {label: "Out of stock", value:"outofstock"}
                    ]
                }, {
                    name: data_product_default.simple_product_fields.sold_individually,
                    label: "Sold Individually",
                    control: "checkbox"
                }, {
                    name: data_product_default.simple_product_fields.weight,
                    label: "Weight (" + initialize_variables['weight_unit'] + ")",
                    control: "input",
                    extraClasses: ["input-numbers","custom-field-numeric"]
                }, {
                    name: data_product_default.simple_product_fields.length,
                    label: "Length (" + initialize_variables['dimension_unit'] + ")",
                    control: "input",
                    extraClasses: ["input-numbers","custom-field-numeric"]
                }, {
                    name: data_product_default.simple_product_fields.width,
                    label: "Width (" + initialize_variables['dimension_unit'] + ")",
                    control: "input",
                    extraClasses: ["input-numbers","custom-field-numeric"]
                }, {
                    name: data_product_default.simple_product_fields.height,
                    label: "Height (" + initialize_variables['dimension_unit'] + ")",
                    control: "input",
                    extraClasses: ["input-numbers","custom-field-numeric"]
                }, {
                    name: "shipping_class",
                    label: "Shipping class",
                    control: "select",
                    options: [
                        {label: "select shipping class", value: "1"}
                    ]
                }, {
                    name: data_product_default.simple_product_fields.purchase_note,
                    label: "Purchase note",
                    control: "input"
                }, {
                    name: "cancel",
                    buttonValue: "Cancel",
                    control: "input-button",
                    extraClasses: ["cancel-add-product-item"]
                }
            ];
        };

        var createForm = function () {
            form = new Backform.Form({
                el: $(formElementID),
                model: new model(),
                fields: fields
            });
        };

        module.renderForm = function (formID) {
            formElementID = formID;
            createModel();
            createFields();
            createForm();
            if(form !== null){
                form.render();
                $(".tab-content-block").trigger(window.fntQEPP.CompatibleSafari.eventRecalculateModalHeight);
            }
        };

        return module.init();
    })();
})(jQuery);