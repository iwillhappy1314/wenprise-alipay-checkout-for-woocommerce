jQuery(document).ready(function($) {
  var loop_count = 50;
  var loop_time = 1000;
  var query_timer = null;
  var countdown_timer = null;
  var wprs_woo_alipay_query_order;
  var confirm_modal = $('#js-alipay-confirm-modal');
  var f2f_qrcode = $('#wprs_wc_alipay_f2f_qrcode');
  var qrcode_loading = $('#js-alipay-qrcode-loading');
  var qrcode_countdown = $('#js-alipay-qrcode-countdown');
  var qrcode_expired = $('#js-alipay-qrcode-expired');
  var refresh_qrcode = $('#js-alipay-refresh-qrcode');

  function show_qrcode_loading() {
    if (f2f_qrcode.length) {
      f2f_qrcode.empty().hide();
    }

    qrcode_expired.hide();
    qrcode_countdown.hide();
    qrcode_loading.show();
  }

  function hide_qrcode_loading() {
    qrcode_loading.hide();
    f2f_qrcode.show();
  }

  function render_f2f_qrcode() {
    if (f2f_qrcode.length && typeof f2f_qrcode.qrcode === 'function') {
      show_qrcode_loading();
      f2f_qrcode.empty().qrcode(f2f_qrcode.data('qrcode'));
      hide_qrcode_loading();
    }
  }

  function get_expires_at() {
    return parseInt(confirm_modal.data('expires_at'), 10) || 0;
  }

  function is_qrcode_expired() {
    var expires_at = get_expires_at();

    return expires_at > 0 && Math.floor(Date.now() / 1000) >= expires_at;
  }

  function format_remaining_time(seconds) {
    var minutes = Math.floor(seconds / 60);
    var remaining_seconds = seconds % 60;

    return minutes + ':' + ( '0' + remaining_seconds ).slice(-2);
  }

  function show_qrcode_expired() {
    qrcode_countdown.hide();
    qrcode_expired.show();

    if (query_timer) {
      clearTimeout(query_timer);
      query_timer = null;
    }

    if (countdown_timer) {
      clearInterval(countdown_timer);
      countdown_timer = null;
    }
  }

  function update_qrcode_countdown() {
    var expires_at = get_expires_at();
    var remaining_seconds = expires_at - Math.floor(Date.now() / 1000);

    if (!f2f_qrcode.length || !expires_at) {
      return;
    }

    if (remaining_seconds <= 0) {
      show_qrcode_expired();
      return;
    }

    f2f_qrcode.show();
    qrcode_expired.hide();
    qrcode_countdown.text('二维码剩余有效时间：' + format_remaining_time(remaining_seconds)).show();
  }

  function start_qrcode_countdown() {
    if (countdown_timer) {
      clearInterval(countdown_timer);
    }

    update_qrcode_countdown();
    countdown_timer = setInterval(update_qrcode_countdown, 1000);
  }

  if (f2f_qrcode.length) {
    render_f2f_qrcode();
    start_qrcode_countdown();
  }

  /**
   * 重新支付页面的支付按钮
   */
  $('#place_order').click(function() {
    var wc_alipay_payment_url  = $(this).
            parent().
            find('input[name="wc-alipay-payment-url"]').
            val(),
        wprs_wc_payment_method = $('input[name="payment_method"]:checked').
            val();

    if (typeof wc_alipay_payment_url !== 'undefined' &&
        wprs_wc_payment_method === 'wprs-wc-alipay') {
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

    if (is_qrcode_expired()) {
      show_qrcode_expired();
      return false;
    }

    $.ajax({
      type   : 'POST',
      url    : WpWooAlipayData.query_url,
      data   : {
        order_id : $('#js-alipay-confirm-modal').data('order_id'),
        order_key: $('#js-alipay-confirm-modal').data('order_key'),
        nonce    : WpWooAlipayData.nonce,
      },
      success: function(data) {
        if (data && data.data && data.data.url &&
            (data.success === true || manual_trigger === true)) {
          location.href = data.data.url;
        } else {
          if (loop_count-- > 0) {
            query_timer = setTimeout(wprs_woo_alipay_query_order, loop_time);
          }
        }
      },
      error  : function() {
        if (loop_count-- > 0) {
          query_timer = setTimeout(wprs_woo_alipay_query_order, loop_time);
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

  refresh_qrcode.click(function() {
    var button = $(this);

    button.prop('disabled', true);
    show_qrcode_loading();

    $.ajax({
      type   : 'POST',
      url    : WpWooAlipayData.refresh_url,
      data   : {
        order_id : confirm_modal.data('order_id'),
        order_key: confirm_modal.data('order_key'),
        nonce    : WpWooAlipayData.nonce,
      },
      success: function(data) {
        if (data && data.success === true && data.data && data.data.qrcode) {
          confirm_modal.data('expires_at', data.data.expires_at);
          confirm_modal.attr('data-expires_at', data.data.expires_at);
          f2f_qrcode.data('qrcode', data.data.qrcode);
          f2f_qrcode.attr('data-qrcode', data.data.qrcode);

          if (query_timer) {
            clearTimeout(query_timer);
            query_timer = null;
          }

          loop_count = 50;
          qrcode_expired.hide();
          render_f2f_qrcode();
          start_qrcode_countdown();
          wprs_woo_alipay_query_order();
        } else {
          hide_qrcode_loading();
          qrcode_expired.show();
          window.alert(data && data.data ? data.data : '二维码重新生成失败，请稍后重试。');
        }
      },
      error  : function() {
        hide_qrcode_loading();
        qrcode_expired.show();
        window.alert('二维码重新生成失败，请稍后重试。');
      },
      complete: function() {
        button.prop('disabled', false);
      },
    });
  });

  wprs_woo_alipay_query_order();

});
