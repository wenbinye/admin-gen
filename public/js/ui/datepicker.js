define(["jquery", "text!./templates/datepicker.html", "moment", "daterangepicker"], function($, datepickerTemplate, moment) {
    var DatePicker = function(options) {
        this.init(options);
    };

    DatePicker.DATE_FORMAT = 'YYYY/MM/DD';

    DatePicker.prototype.init = function(options) {
        var self = this;
        var $container = $(datepickerTemplate).appendTo(options.container);
        var format = options.format || DatePicker.DATE_FORMAT;
        var startDate = options.start || moment().subtract(6, 'days');
        var endDate = options.end || moment();
        this.format = format;
        this.startDate = startDate;
        this.endDate = endDate;
        
        $('span', $container).html(startDate.format(format) + ' - ' + endDate.format(format));
        $container.daterangepicker({
            startDate: startDate,
            endDate: endDate,
            format: format,
            opens: 'left',
            locale: {
                customRangeLabel: '选择日期'
            },
            ranges: {
                '昨天': [moment().subtract(1, 'days'), moment()],
                '一周内': [moment().subtract(6, 'days'), moment()],
                '一月内': [moment().subtract(29, 'days'), moment()],
                '三个月': [moment().subtract(90, 'days'), moment()]
            }
        }, function(start, end, label) {
            self.startDate = start;
            self.endDate = end;
            $('span', $container).html(start.format(format) + ' - ' + end.format(format));
            if ( options.callback ) {
                options.callback(start, end, label);
            }
        });
        return this;
    };

    DatePicker.prototype.getStartDate = function() {
        return this.startDate.format(this.format);
    }

    DatePicker.prototype.getEndDate = function() {
        return this.endDate.format(this.format);
    }

    return DatePicker;
});
