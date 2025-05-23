humhub.module('admin.PendingRegistrations', function (module, require, $) {
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;
    var client = require('client');

    var PendingRegistrations = Widget.extend();

    PendingRegistrations.prototype.resendAllSelected = function (evt) {
        var that = this;
        client.post(evt).then(function () {
            var keys = $("#grid").yiiGridView("getSelectedRows");
            $.ajax({
                url: that.options.urlResendSelected,
                type: "POST",
                data: {id: keys},
            })
        }).catch(function (e) {
            module.log.error(e, true);
        })
    };

    PendingRegistrations.prototype.deleteAllSelected = function (evt) {
        var that = this;
        client.post(evt).then(function () {
            var keys = $("#grid").yiiGridView("getSelectedRows");
            $.ajax({
                url: that.options.urlDeleteSelected,
                type: "POST",
                data: {id: keys},
            })
        }).catch(function (e) {
            module.log.error(e, true);
        })
    };

    PendingRegistrations.prototype.resendAll = function (evt) {
        client.post(evt).catch(function () {
            module.log.error(e, true);
        })
    };

    PendingRegistrations.prototype.deleteAll = function (evt) {
        client.post(evt).catch(function () {
            module.log.error(e, true);
        })
    };

    PendingRegistrations.prototype.init = function () {
        var that = this;
        this.$.find("input").change(function () {
            var $selection = that.$.find(':checked')
            if ($selection.length > 1) {
                $('.resend-all').html(that.options.noteResendSelected);
                $('.resend-all').attr('data-action-click', 'resendAllSelected');
                $('.resend-all').attr('data-action-click-url', that.options.urlResendSelected);
                $('.delete-all').html(that.options.noteDeleteSelected);
                $('.delete-all').attr('data-action-click', 'deleteAllSelected');
                $('.delete-all').attr('data-action-click-url', that.options.urlDeleteSelected);
            } else {
                $('.resend-all').html(that.options.noteResendAll);
                $('.resend-all').attr('data-action-click', 'resendAll');
                $('.resend-all').attr('data-action-click-url', that.options.urlResendAll);
                $('.delete-all').html(that.options.noteDeleteAll);
                $('.delete-all').attr('data-action-click', 'deleteAll');
                $('.delete-all').attr('data-action-click-url', that.options.urlDeleteAll);
            }
        });
    };

    // Export a single class
    module.export = PendingRegistrations;
});
