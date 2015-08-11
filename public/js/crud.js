define([
    "jquery",
    "util/form",
    "ui/dialog",
    "ui/alert",
    "ui/bootstrap.datatable",
    "hogan"
], function($, Form, Dialog, Alert) {
    var Record = {
        baseUrl: undefined,
        create: function(data, success, error) {
            this.save(data, success, error, true);
        },
        update: function(data, success, error) {
            this.save(data, success, error, false);
        },
        save: function(data, successCallback, errorCallback, isCreate) {
            $.ajax({
                "type": "POST",
                "url": this.baseUrl + "/" + (isCreate ? "create" : "update"),
                "data": data,
                "success": function(res) {
                    successCallback(res);
                },
                "error": function(xhr, status, error) {
                    var result = xhr.responseText;
                    if (result.substr(0, 1) == '{') {
                        errorCallback($.parseJSON(result));
                    } else {
                        Dialog.alert("Operation failed", result);
                    }
                }
            });
        },
        del: function(id, callback) {
            $.ajax({
                "type": "POST",
                "url": this.baseUrl + "/delete/" + id,
                "data": Form.getCsrfToken({}),
                "success": function() {
                    callback();
                },
                "error": this.errorHandler
            });
        },
        errorHandler: function(xhr, status, error) {
            var result = xhr.responseText;
            var msg;
            if (result.substr(0, 1) == '{') {
                msg = $.parseJSON(result).error;
            } else {
                msg = result;
            }
            Dialog.alert("Operation failed", msg);
        }
    };

    var App = function() {};

    App.prototype.init = function(options) {
        options = $.extend(options, {
            container: '#data-table'
        });
        Record.baseUrl = options.baseUrl;
        var self = this;
        this.records = {};
        var $container = $(options.container);
        var btnTemplate = Hogan.compile($(".buttons-tmpl", $container).text());
        var $table = $(".table", $container);
        var ncol = options.columns.length;
        options.columns.push({
			"orderable": false,
			"data": null,
			"defaultContent": '',
		});
        var dataTable = $table.dataTable({
		    serverSide: true,
		    ajax: {
                // fix uri too long
                type: ncol < 10 ? "GET" : "POST",
                url: Record.baseUrl + '/list'
            },
		    columns: options.columns,
		    rowCallback: function(row, data) {
                self.records[data[options.primary_key]] = data;
			    $('td:eq('+ ncol +')', row).html(btnTemplate.render(data));
		    }
	    }).DataTable();
        var editDialog = new EditDialog().init({
            container: $(".edit-dialog", $container),
            name: options.name,
            display_column: options.display_column,
            callback: function() {
                dataTable.ajax.reload(null, false);
            }
        });
        $table.delegate(".delete-btn", "click", function() {
            var $this = $(this);
            if (!confirm("Are you sure? This action cannot be undone.")) {
                return false;
            }
            var id = $this.parent().data('id');
            Record.del(id, function() {
                dataTable.ajax.reload(null, false);
            });
        });
        $table.delegate(".edit-btn", "click", function() {
            var id = $(this).parent().data('id');
            editDialog.show(self.records[id]);
        });
        $(".create-btn", $container).click(function() {
            editDialog.show();
        });
    };

    var EditDialog = function() {};

    EditDialog.prototype.init = function(options) {
        var self = this;
        var $dialog = options.container;
        $(".btn-primary", $dialog).click($.proxy(this, 'submit'));
        this.$form = $("form", $dialog);
        this.$dialog = $dialog;
        this.alert = new Alert({
            container: $("[role=alert]", $dialog)
        });
        this.callback = options.callback;
        this.name = options.name;
        this.display_column = options.display_column;
        return this;
    };

    EditDialog.prototype.show = function(record) {
        var self = this;
        $(".error-desc", this.$form).addClass('hide');
        if (record) {
            this.create = false;
            Form.resetForm(this.$form, record);
            var display = this.name;
            if (this.display_column) {
                display = record[this.display_column];
            }
            $("h2", this.dialog).text("Update " + display);
        } else {
            this.create = true;
            Form.resetForm(this.$form, {});
            $("h2", this.dialog).text("Create " + this.name);
        }
        this.$dialog.modal();
    };

    EditDialog.prototype.submit = function(event) {
        var self = this;
        var data = Form.getForm(this.$form);
        var err = $.proxy(this, "showError");
        var success = $.proxy(this, "onSave");
        if (this.create) {
            Record.create(data, success, err);
        } else {
            Record.update(data, success, err);
        }
    };

    EditDialog.prototype.onSave = function(data) {
        this.$dialog.modal('hide');
        this.callback(data);
    };

    EditDialog.prototype.showError = function(error) {
        if (error.errors) {
            var errors = error.errors;
            for (var name in errors) {
                if (!errors.hasOwnProperty(name)) {
                    continue;
                }
                var msgs = errors[name];
                var $group = $("[name="+name+"]", this.$form).parents(".form-group");
                $group.addClass('has-error');
                $(".error-desc", $group).removeClass("hide")
                    .text(msgs.join("<br/>"));
            }
        } else if (error.error) {
            this.alert.error(error.error);
        }
    };
    return App;
});
