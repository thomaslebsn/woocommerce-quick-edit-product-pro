(function ($) {
    window.fntQEPP =  window.fntQEPP || {};
    window.fntQEPP.formExternalProduct = (function(){
        var module = {},
            model = null,
            fields = null,
            form = null,
            formElementID = "#form-add-new-external-product";
        module.init = function(){
            return module;
        };

        var createModel = function () {
            model = Backbone.Model.extend({
                defaults: {
                    id: 0,
                    title: ""
                },
                validate: function(attributes, options) {
                    this.errorModel.clear();
                    var title = this.get("title");
                    if(title == null || title == ""){
                        this.errorModel.set({title: "Title is required !"});
                    }

                    if (!_.isEmpty(_.compact(this.errorModel.toJSON())))
                        return "Validation errors. Please fix.";
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
                    name: data_product_default.external_product_fields.post_title, // The key of the model attribute
                    label: "Product Name", // The label to display next to the control
                    control: "input", // This will be converted to InputControl and instantiated from the proper class under the Backform namespace
                    extraClasses: ["post-title","validate[required]"],
                    required: true
                }, {
                    name: data_product_default.external_product_fields.product_content,
                    label: "Description",
                    control: "textarea",
                    extraClasses: ["custom-rte-style"]
                }, {
                    name: data_product_default.external_product_fields.product_excerpt,
                    label: "Short Description",
                    control: "textarea",
                    extraClasses: ["custom-rte-style"]
                    //helpMessage: "Be creative!"
                }, {
                    name: data_product_default.external_product_fields.product_cat,
                    label: "Category",
                    control: "dropdown-list",
                    data: replaceHtmlCategory(data_product_default.category,data_product_default.external_product_fields.product_cat,'tag-simple-category','simple-category form-control custom-select-style')
                }, {
                    name: data_product_default.external_product_fields.product_tag,
                    label: "Product tags",
                    control: "input"
                }, {
                    name: "product_type",
                    control: "input-hidden",
                    hiddenValue: "external"
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
                    name: data_product_default.external_product_fields.sku,
                    label: "SKU",
                    control: "input",
                    extraClasses: ["new-sku"]
                }, {
                    name: data_product_default.external_product_fields.regular_price,
                    label: "Regular price",
                    control: "input",
                    extraClasses: ["validate[custom[number]]"]
                }, {
                    name: data_product_default.external_product_fields.sale_price,
                    label: "Sale price",
                    control: "input",
                    extraClasses: ["validate[custom[number]]"]
                }, {
                    name: data_product_default.external_product_fields.comment_status,
                    label: "Allow comment",
                    control: "checkbox"
                },  {
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
                fields: fields, // Will get converted to a collection of Backbone.Field models
                events: {
                    "submit": function(e) {
                        e.preventDefault();
                        var submit = form.fields.get("submit");
                        if (form.model.isValid()) {
                            submit.set({status: "success", message: "Success!"});
                        } else {
                            submit.set({status: "error", message: form.model.validationError});
                        }
                        return false;
                    }
                }
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