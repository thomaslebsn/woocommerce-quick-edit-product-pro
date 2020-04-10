var CheckListControl = Backform.CheckListControl = Backform.InputControl.extend({
    defaults: {
        type: "checkbox",
        label: "",
        options: [],
        extraClasses: [],
        labelWrapperCheckBoxClasses: [],
        helpMessage: null,
        isSeparatedColumns: false,
        numOfInstancePerColumn: 4,
        populatedCheckbox:[]
    },
    template: _.template([
        '<label class="<%=Backform.controlLabelClassName%>"><%=label%>',
        '    <% if(required) { %>',
        '        <span class="required-field">*</span>',
        '    <% } %>',
        '</label>',
        '<div class="<%=Backform.controlsClassName%> checklist">',
        '   <% var columnsCounter = isSeparatedColumns ? Math.ceil(options.length / numOfInstancePerColumn) : 1; %>',
        '   <% var nextIndex = 0; %>',
        '   <% var indexCheckbox = numOfInstancePerColumn; %>',
        '   <% while ( columnsCounter > 0 ) { %>',
        '   <ul class="nav nav-list">',
        '       <% for (var i = nextIndex; i < options.length; i++) { %>',
        '           <% var option = options[i]; %>',
        '           <li>',
        '               <label class="<%=labelWrapperCheckBoxClasses.join(\' \')%>">',
    '                       <input type="<%=type%>" class="<%=extraClasses.join(\' \')%>" name="<%=name%>[]" value="<%-formatter.fromRaw(option.value)%>" <%=jQuery.inArray(option.value, populatedCheckbox) > -1 ? "checked=\'checked\'" : ""%> <%=disabled ? "disabled" : ""%> <%=required ? "required" : ""%> /> <%-option.label%>',
        '               </label>',
        '           </li>',
        '           <% if(i == (indexCheckbox - 1) || i == (options.length - 1)) { %>',
        '              <% nextIndex = i + 1; %>',
        '              <% columnsCounter -= 1; %>',
        '              <% indexCheckbox += numOfInstancePerColumn; %>',
        '              <% indexCheckbox = (indexCheckbox > options.length) ? options.length : indexCheckbox; %>',
        '              <% break; %>',
        '           <% } %>',
        '       <% } %>',
        '   </ul>',
        '<% } %>',
        '</div>',
        '  <% if (helpMessage && helpMessage.length) { %>',
        '    <span class="<%=Backform.helpMessageClassName%>"><%=helpMessage%></span>',
        '  <% } %>'
    ].join("\n")),
    formatter: Backform.JSONFormatter,
    getValueFromDOM: function() {
        var selectedOptions = [];
        this.$el.find("input:checked").each(function(){
            selectedOptions.push(jQuery(this).val());
        });
        return this.formatter.toRaw(JSON.stringify(selectedOptions), this.model);
    }
});

var DropdownListControl = Backform.DropdownListControl = Backform.SelectControl.extend({
    defaults: {
        type: "multi-select",
        label: "",
        options: [],
        extraClasses: [],
        labelWrapperSelectClasses: "",
        data: "",
        helpMessage: null
    },
    template: _.template([
        '<label class="<%=Backform.controlLabelClassName%>"><%=label%>',
        '    <% if(required) { %>',
        '        <span class="required-field">*</span>',
        '    <% } %>',
        '</label>',
        '<div class="<%=Backform.controlsClassName%> category-custom"><%=data%></div>'
    ].join("\n")),
    formatter: Backform.JSONFormatter,
    getValueFromDOM: function() {
        var selectedOptions = this.$el.find('.form-control ').val();
        //selectedOptions[this.$el.find('.form-control ').val()] = this.$el.find('.form-control ').text();
        //console.log(selectedOptions);
        return this.formatter.toRaw(JSON.stringify(selectedOptions), this.model);
    }
});

var InputHiddenControl = Backform.InputHiddenControl = Backform.InputControl.extend({
    defaults: {
        maxlength: 255,
        hiddenValue: "",
        helpMessage: null,
        extraClasses: ""
    },
    template: _.template([
        '<input type="hidden" name="<%-name%>" class ="<%=extraClasses%>" value="<%=hiddenValue%>" placeholder="<%-placeholder%>" />'
    ].join("\n")),
    getValueFromDOM: function() {
        return this.formatter.toRaw(this.$el.find("input").val(), this.model);
    }
});

var InputButtonControl = Backform.InputButtonControl = Backform.InputControl.extend({
    defaults: {
        name: "",
        label: "",
        buttonValue: "Button name",
        extraClasses: ""
    },
    template: _.template([
        '<label class="<%=Backform.controlLabelClassName%>"><%=label%>',
        '    <% if(required) { %>',
        '        <span class="required-field">*</span>',
        '    <% } %>',
        '</label>',
        '<div class="col-sm-10">',
        '<input type="button" name="<%-name%>" class ="<%=extraClasses%> btn" value="<%=buttonValue%>" />',
        '</div>'
    ].join("\n")),
    getValueFromDOM: function() {
        return this.formatter.toRaw(this.$el.find("input").val(), this.model);
    }
});

var InputImageControl = Backform.InputImageControl = Backform.InputControl.extend({
    defaults: {
        name: "",
        label: "",
        extraClasses: "",
        columnName: "",
        galleryClass: ""
    },
    template: _.template([
        '<label class="<%=Backform.controlLabelClassName%>"><%=label%>',
        '    <% if(required) { %>',
        '        <span class="required-field">*</span>',
        '    <% } %>',
        '</label>',
        '<div class ="wrap-image bootstrap-wrapper col-sm-10">',
            '<span class="<%=extraClasses%>" column-name="<%=columnName%>">',
                '<span class="wrap-gallery-image bootstrap-wrapper wrapper-gallery-plus <%=galleryClass%>">',
                    '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>',
                '</span>',
            '</span>',
        '</div>'
    ].join("\n")),
    getValueFromDOM: function() {
        return this.formatter.toRaw(this.$el.find("input").val(), this.model);
    }
});


var ColorPickerControl = Backform.ColorPickerControl = Backform.InputControl.extend({
    defaults: {
        label: "",
        helpMessage: null,
        extraClasses: []
    },
    template: _.template([
        '<label class="<%=Backform.controlLabelClassName%>"><%=label%>',
        '    <% if(required) { %>',
        '        <span class="required-field">*</span>',
        '    <% } %>',
        '</label>',
        '<div class="<%=Backform.controlsClassName%> input-group input-color-picker">',
        '    <div class="wrapper-inner-color-picker"> ',
        '        <input type="text" name="<%=name%>"  value="<%=rawValue%>" class="<%=Backform.controlClassName%>" />',
        '        <span class="input-group-addon"><i></i></span>',
        '    </div>',
        '       <% if (helpMessage && helpMessage.length) { %>',
        '           <div class="wrapper-inner-help-block">',
        '               <span class="<%=Backform.helpMessageClassName%>"><%=helpMessage%></span>',
        '           </div>',
        '       <% } %>',
        '</div>',
    ].join("\n")),
    getValueFromDOM: function() {
        return this.formatter.toRaw(this.$el.find("input").val(), this.model);
    }
});

var VariableAttributeControl = Backform.VariableAttributeControl = Backform.SelectControl.extend({
    defaults: {
        type: "multi-select",
        label: "",
        name: "",
        options: [],
        extraClasses: [],
        labelWrapperSelectClasses: "",
        data: "",
        helpMessage: null
    },
    template: _.template([
        '<label class="<%=Backform.controlLabelClassName%> variable-attribute-label"><%=label%>',
        '    <% if(required) { %>',
        '        <span class="required-field">*</span>',
        '    <% } %>',
        '</label>',
        '<div class="<%=Backform.controlsClassName%> wrap-variable-attribute">',
        '<select name="<%=name%>" class="form-control <%=extraClasses%>" multiple="multiple">',
        '<% _.each(data, function(item, index){ %>',
            '<option value="<%=index%>"><%=(item != null ? item : index)%></option>',
        '<%});%>',
        '</select>',
        '</div>'
    ].join("\n")),
    formatter: Backform.JSONFormatter,
    getValueFromDOM: function() {
        var selectedOptions = [];
        this.$el.find("option:selected").each(function(){
            selectedOptions.push(jQuery(this).val());
        });
        return this.formatter.toRaw(JSON.stringify(selectedOptions), this.model);
    }
});
