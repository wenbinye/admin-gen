define(["jquery", "text!./templates/alert.html", "bootstrap", "hogan"], function($, alertTemplate) {
    var Alert = function(options) {
        this.options = $.extend({
            timeout: 5000,
            template: alertTemplate
        }, options || {});
        this.template = Hogan.compile(this.options.template);
    };

    Alert.prototype.show = function(message, type) {
        var self = this;
        var $alert = $(this.options.container);
        $alert.html(this.template.render({ message: message }));
        if ( typeof type !== "undefined" ) {
            $(".alert", $alert).addClass('alert-' + type);
        }
        if ( this.timer ) {
            window.clearTimeout(this.timer);
        }
        this.timer = window.setTimeout($.proxy(this.hide, this), this.options.timeout);
    };

    Alert.prototype.info = function(message) {
        this.show(message, 'info');
    }

    Alert.prototype.error = function(message) {
        this.show(message, 'danger');
    }

    Alert.prototype.warn = function(message) {
        this.show(message, 'warning');
    }

    Alert.prototype.hide = function() {
        $(".alert", this.options.container).alert('close');
        this.timer = undefined;
    };

    return Alert;
});
