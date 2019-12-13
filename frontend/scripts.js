(function ($) {
    var loop_count = 50;
    var loop_time = 1000;
    var wprs_woo_alipay_query_order;
    var confirm_modal = $('#js-alipay-confirm-modal');

    $('#wprs-alipay-form').submit();

    /**
     * 点击提交支付表单
     */
    if (confirm_modal.length !== 0) {
        $.blockUI({
            message: confirm_modal,
            css: {
                width: '500px',
                height: '290px',
            },
        });
    }

    /**
     * 查询订单支付结果
     * @param manual 是否手动检查，手动检查时，无论拍支付是否成功，均需要跳转页面
     */
    wprs_woo_alipay_query_order = function (manual) {

        var manual_trigger = arguments[0] ? arguments[0] : false;

        if (confirm_modal.length === 0) {
            return false;
        }

        $.ajax({
            type: 'POST',
            url: WpWooAlipayData.query_url,
            data: {
                order_id: $('#js-alipay-confirm-modal').data('order_id'),
            },
            success: function (data) {
                if (data && data.success === true || manual_trigger === true) {
                    location.href = data.data;
                } else {
                    if (loop_count-- > 0) {
                        setTimeout(wprs_woo_alipay_query_order, loop_time);
                    }
                }
            },
            error: function (data) {
                if (loop_count-- > 0) {
                    setTimeout(wprs_woo_alipay_query_order, loop_time);
                }
            },
        });
    };

    /**
     * 支付成功后，如果没有自动跳转，点击按钮查询订单并跳转支付结果
     */
    $('#js-alipay-success, #js-alipay-fail').click(function () {
        $.blockUI({message: '<div style="padding: 1rem;">订单查询中...</div>'});

        wprs_woo_alipay_query_order(true);
    });

    wprs_woo_alipay_query_order();

})(jQuery);