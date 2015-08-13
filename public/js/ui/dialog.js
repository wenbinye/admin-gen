define([
    "jquery",
    "text!./templates/dialog.html",
    "text!./templates/dialog-button.html",
    "bootstrap",
    "hogan"
], function($, dialogTemplate, buttonTemplate) {
    var hideTime = 0;
    var Modal = $.fn.modal.Constructor;
    var delay = Modal.TRANSITION_DURATION + Modal.BACKDROP_TRANSITION_DURATION;
    
    var getContainer = function() {
        var $el = $("#ui-dialog");
        if ($el.size() == 0) {
            $el = $(dialogTemplate).appendTo(document.body);
            $el.on('hide.bs.modal', function() {
                hideTime = new Date().getTime();
            });
        }
        return $el;
    }

    var Dialog = function(options) {
        this.options = options;
        this.template = Hogan.compile(buttonTemplate);
    };

    Dialog.prototype.show = function() {
        var self = this;
        var showTime = new Date().getTime();
        if (hideTime && showTime - hideTime < delay) {
            setTimeout(function() {
                self.show();
            }, delay);
            return this;
        }
        var $dialog = getContainer();
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

    Dialog.create = function(options) {
        return new Dialog(options);
    };

    Dialog.alert = function(title, msg) {
        return new Dialog({
            title: title,
            message: msg,
            buttons: [{text: "Close", type: "default"}]
        }).show();
    };

    Dialog.confirm = function(title, msg, callback) {
        return new Dialog({
            title: title,
            message: msg,
            buttons: [
                {
                    text: "Cancel",
                    type: "default",
                    click: function(dialog) {
                        callback(false);
                    }
                },
                {
                    text: "Ok",
                    type: "primary",
                    click: function(dialog) {
                        callback(true);
                    }
                }
            ]
        }).show();
    };

    return Dialog;
});
