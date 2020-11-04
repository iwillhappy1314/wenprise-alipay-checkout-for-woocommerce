jQuery(document).ready(function($) {
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
     * 重新支付页面的支付按钮
     */
    $('#place_order').click(function() {
        var wc_alipay_payment_url = $(this).parent().find('input[name="wc-alipay-payment-url"]').val(),
            wprs_wc_payment_method = $('input[name="payment_method"]:checked').val();

        if (typeof wc_alipay_payment_url !== 'undefined' && wprs_wc_payment_method === 'wprs-wc-alipay') {
            var alipay_window = window.open(wc_alipay_payment_url, '_blank');
            alipay_window.focus();
        }
    });

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
     *
     * @type {{
     * to_alipay : (function() : boolean),
     * init : init,
     * $checkout_form : (jQuery|HTMLElement),
     * submit_error : submit_error,
     * blockOnSubmit : blockOnSubmit,
     * scroll_to_notices : scroll_to_notices,
     * detachUnloadEventsOnSubmit : detachUnloadEventsOnSubmit,
     * attachUnloadEventsOnSubmit : attachUnloadEventsOnSubmit}}
     */
    var wc_alipay_checkout = {
        $checkout_form: $('form.checkout'),

        init: function() {
            this.$checkout_form.on('checkout_place_order_wprs-wc-alipay', this.to_alipay);
        },

        to_alipay: function() {
            event.preventDefault();

            var $form = $(this);

            // 事先打开一个窗口，Ajax 成功后替换 location, 以解决弹出窗口被屏蔽的问题
            var alipay_window = window.open(WpWooAlipayData.bridge_url, '_blank');

            $form.addClass('processing');

            wc_alipay_checkout.blockOnSubmit($form);

            // Attach event to block reloading the page when the form has been submitted
            wc_alipay_checkout.attachUnloadEventsOnSubmit();

            $.ajax({
                type    : 'POST',
                url     : wc_checkout_params.checkout_url,
                data    : $form.serialize(),
                dataType: 'json',
                success : function(result) {
                    // Detach the unload handler that prevents a reload / redirect
                    wc_alipay_checkout.detachUnloadEventsOnSubmit();

                    try {
                        if ('success' === result.result) {
                            if (-1 === result.redirect.indexOf('https://') || -1 ===
                                result.redirect.indexOf('http://')) {
                                alipay_window.location = result.payment_url;
                                alipay_window.focus();

                                window.location = result.redirect;

                                return false;
                            } else {
                                alipay_window.location = decodeURI(result.payment_url);
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
                            wc_alipay_checkout.submit_error(result.messages);
                        } else {
                            wc_alipay_checkout.submit_error(
                                '<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error +
                                '</div>'); // eslint-disable-line max-len
                        }
                    }
                },
                error   : function(jqXHR, textStatus, errorThrown) {
                    // Detach the unload handler that prevents a reload / redirect
                    wc_alipay_checkout.detachUnloadEventsOnSubmit();

                    wc_alipay_checkout.submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
                },
            });

            return false;
        },

        blockOnSubmit: function($form) {
            var form_data = $form.data();

            if (1 !== form_data['blockUI.isBlocked']) {
                $form.block({
                    message   : null,
                    overlayCSS: {
                        background: '#fff',
                        opacity   : 0.6,
                    },
                });
            }
        },

        attachUnloadEventsOnSubmit: function() {
            $(window).on('beforeunload', this.handleUnloadEvent);
        },

        detachUnloadEventsOnSubmit: function() {
            $(window).unbind('beforeunload', this.handleUnloadEvent);
        },

        handleUnloadEvent: function(e) {
            // Modern browsers have their own standard generic messages that they will display.
            // Confirm, alert, prompt or custom message are not allowed during the unload event
            // Browsers will display their own standard messages

            // Check if the browser is Internet Explorer
            if ((navigator.userAgent.indexOf('MSIE') !== -1) || (!!document.documentMode)) {
                // IE handles unload events differently than modern browsers
                e.preventDefault();
                return undefined;
            }

            return true;
        },

        submit_error: function(error_message) {
            $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
            wc_alipay_checkout.$checkout_form.prepend(
                '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>'); // eslint-disable-line max-len
            wc_alipay_checkout.$checkout_form.removeClass('processing').unblock();
            wc_alipay_checkout.$checkout_form.find('.input-text, select, input:checkbox').trigger('validate').blur();
            wc_alipay_checkout.scroll_to_notices();
            $(document.body).trigger('checkout_error');
        },

        scroll_to_notices: function() {
            var scrollElement = $('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout');

            if (!scrollElement.length) {
                scrollElement = $('.form.checkout');
            }

            $.scroll_to_notices(scrollElement);
        },

    };

    if('bridge_url' in WpWooAlipayData){
        wc_alipay_checkout.init();
    }

});