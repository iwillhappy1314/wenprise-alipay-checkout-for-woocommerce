(function($) {
    var loop_count = 50;
    var loop_time = 1000;
    var wprs_woo_alipay_query_order;
    var confirm_modal = $('#js-alipay-confirm-modal');

    /**
     * 点击提交支付表单
     */
    if (confirm_modal.length !== 0) {
        $.blockUI({
            message: confirm_modal,
            css    : {
                width : '500px',
                height: '290px',
            },
        });
    }

    /**
     * 查询订单支付结果
     * @param manual 是否手动检查，手动检查时，无论拍支付是否成功，均需要跳转页面
     */
    wprs_woo_alipay_query_order = function(manual) {

        var manual_trigger = arguments[0] ? arguments[0] : false;

        if (confirm_modal.length === 0) {
            return false;
        }

        $.ajax({
            type   : 'POST',
            url    : WpWooAlipayData.query_url,
            data   : {
                order_id: $('#js-alipay-confirm-modal').data('order_id'),
            },
            success: function(data) {
                if (data && data.success === true || manual_trigger === true) {
                    location.href = data.data;
                } else {
                    if (loop_count-- > 0) {
                        setTimeout(wprs_woo_alipay_query_order, loop_time);
                    }
                }
            },
            error  : function(data) {
                if (loop_count-- > 0) {
                    setTimeout(wprs_woo_alipay_query_order, loop_time);
                }
            },
        });
    };

    /**
     * 支付成功后，如果没有自动跳转，点击按钮查询订单并跳转支付结果
     */
    $('#js-alipay-success, #js-alipay-fail').click(function() {
        $.blockUI({message: '<div style="padding: 1rem;">订单查询中...</div>'});

        wprs_woo_alipay_query_order(true);
    });

    wprs_woo_alipay_query_order();

    /**
     * 修改点击用支付宝支付按钮的操作
     * @type {*|jQuery|HTMLElement}
     */
    var $form = $('form.woocommerce-checkout');

    $form.on('checkout_place_order_wprs-wc-alipay', function(event) {

        event.preventDefault();

        $form.addClass('processing');

        $form.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        $.ajax({
            type    : 'POST',
            url     : wc_checkout_params.checkout_url,
            data    : $form.serialize(),
            dataType: 'json',
            success : function(result) {
                try {
                    if ('success' === result.result) {
                        if (-1 === result.redirect.indexOf('https://') || -1 === result.redirect.indexOf('http://')) {
                            var alipay_window = window.open(result.payment_url, '_blank');
                            alipay_window.focus();

                            window.location = result.redirect;

                            return false;
                        } else {
                            var alipay_window = window.open(decodeURI(result.payment_url), '_blank');
                            alipay_window.focus();

                            window.location = decodeURI(result.redirect);
                        }
                    } else if ('failure' === result.result) {
                        throw 'Result failure';
                    } else {
                        throw 'Invalid response';
                    }
                } catch (err) {
                    // Reload page
                    if (true === result.reload) {
                        window.location.reload();
                        return;
                    }

                    // Trigger update in case we need a fresh nonce
                    if (true === result.refresh) {
                        $(document.body).trigger('update_checkout');
                    }

                    // Add new errors
                    if (result.messages) {
                        wc_checkout_form.submit_error(result.messages);
                    } else {
                        wc_checkout_form.submit_error(
                            '<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>'); // eslint-disable-line max-len
                    }
                }
            },
            error   : function(jqXHR, textStatus, errorThrown) {
                // Detach the unload handler that prevents a reload / redirect
                wc_checkout_form.detachUnloadEventsOnSubmit();

                wc_checkout_form.submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
            },
        });

        return false;
    });

})(jQuery);