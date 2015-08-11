define(["jquery", "text!./templates/dialog.html", "text!./templates/dialog-button.html", "bootstrap", "hogan"], function($, dialogTemplate, buttonTemplate) {
    var Dialog = function(options) {
        this.options = options;
        this.template = Hogan.compile(buttonTemplate);
    };

    var getDialogContainer = function() {
        var $el = $("#txf-ui-dialog");
        if ($el.size() == 0) {
            $el = $(dialogTemplate).appendTo(document.body);
        }
        return $el;
    }

    Dialog.prototype.show = function() {
        var self = this;
        var $dialog = getDialogContainer();
        $(".modal-title", $dialog).text(this.options.title);
        $(".modal-body p", $dialog).text(this.options.message);
        if ( this.options.buttons ) {
            var i, len, but, html, el,
            buttons = this.options.buttons,
            footer = $(".modal-footer", $dialog);
            footer.empty();
            for ( i=0,len=buttons.length; i<len; i++ ) {
                but = buttons[i];
                html = this.template.render(but);
                el = $(html);
                if ( but.click ) {
                    el.click((function(but) {
                        return function() { return but.click(self); }
                    })(but));
                }
                footer.append(el);
            }
            footer.show();
        } else {
            $(".modal-footer", $dialog).hide();
        }
        $dialog.modal();
        this.container = $dialog;
        return this;
    };

    Dialog.prototype.hide = function() {
        this.container.modal('hide');
    };

    Dialog.newDialog = function(options) {
        return new Dialog(options);
    };

    Dialog.showDialog = function(title, msg) {
        return new Dialog({
            title: title,
            message: msg,
            buttons: [{text: "关闭"}]
        }).show();
    };

    return Dialog;
});
