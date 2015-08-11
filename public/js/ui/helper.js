define(['jquery', './dialog'], function($, Dialog) {
    var Helper = {};

    Helper.handleServerError = function(xhr) {
        if ( xhr.status == 401 ) {
            Dialog.showDialog({
                title: "服务器错误",
                message: "登录已经过期，请重新登录",
                buttons: [
                    {
                        text: "确定",
                        type: "primary",
                        click: function() {
                            window.location.reload();
                        }
                    }
                ]
            });
        } else {
            var options = {title: '操作失败', 'message': ''};
            try {
                var data = $.parseJSON(xhr.responseText);
                if ( data.error ) {
                    options.message = data.error;
                } else {
                    options.message = '错误代码： ' + xhr.status;
                }
            } catch ( e ) {
                options.message = '服务器错误';
            }
            Dialog.showDialog(options);
        }
    };

    Helper.alertServerError = function(xhr, alert) {
        try {
            var data = $.parseJSON(xhr.responseText);
            if ( data.error ) {
                alert.error("服务器错误["+ data.error +"]，请联系管理员");
            } else {
                alert.error("服务器错误["+ xhr.status +"]，请联系管理员");
            }
        } catch ( e ) {
            alert.error("数据错误，请联系管理员");
        }
    };

    Helper.openInNewTab = function(url){
        var win=window.open(url, '_blank');
        win.focus();
    };

    return Helper;
});
