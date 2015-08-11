define(["jquery"], function($) {
    "use strict";
    var form = {
        getForm: function($box) {
            var form = {};
            $('input, textarea, select', $box).each(function(i, obj) {
                var $this = $(this);
                var name = $this.attr('name');
                if(!name) {
                    return;
                }
                var type = $this.attr('type');
                var val = $.trim($this.val());
                if (type == 'radio' && !$this.prop('checked')) {
                    return;
                }
                if (type == 'checkbox') {
                    val = $this.prop('checked');
                }
                form[name] = val;
            });
            return this.getCsrfToken(form);
        },

        getCsrfToken: function(data) {
            var token = undefined;
            var $tokenInput = $("#csrf-token");
            if ($tokenInput.size() > 0) {
                data[$tokenInput.attr('name')] = $tokenInput.val();
            }
            return data;
        },

        resetForm: function($box, form) {
            $('input, textarea', $box).each(function() {
                var $this = $(this);
                var name = $this.attr('name');
                if ( $this.attr('type') == 'checkbox'
                    || $this.attr('type') == 'radio') {
                    $this.prop('checked', false);
                } else {
                    if ($this.is('textarea')) {
                        var wysihtml = $this.data('wysihtml5');
                        if (wysihtml) {
                            wysihtml.editor.clear().setValue(form[name]);
                        } else {
                            $this.val(form[name]);
                        }
                    } else {
                        $this.val(form[name]);
                    }
                }
            });
        },
        
        equals: function(form1, form2) {
            for(var k in form1) {
                if(form1.hasOwnProperty(k)) {
                    if(form1[k] !== form2[k]) return false;
                }
            }
            return true;
        }
    };

    return form;
});
