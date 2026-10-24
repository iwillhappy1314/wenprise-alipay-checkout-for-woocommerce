jQuery(document).ready(function($) {
  var loop_count = 50;
  var loop_time = 1000;
  var query_timer = null;
  var countdown_timer = null;
  var wprs_woo_alipay_query_order;
  var confirm_modal = $('#js-alipay-confirm-modal');
  var f2f_qrcode = $('#wprs_wc_alipay_f2f_qrcode');
  var qrcode_loading = $('#js-alipay-qrcode-loading');
  var qrcode_status = $('.wprs-qrcode-status');
  var qrcode_countdown = $('#js-alipay-qrcode-countdown');
  var qrcode_expired = $('#js-alipay-qrcode-expired');
  var refresh_qrcode = $('#js-alipay-refresh-qrcode');

  function get_i18n_message(key, fallback) {
    if (WpWooAlipayData.i18n && WpWooAlipayData.i18n[key]) {
      return WpWooAlipayData.i18n[key];
    }

    return fallback;
  }

  function should_continue_query() {
    var expires_at = get_expires_at();

    if (!expires_at) {
      return loop_count-- > 0;
    }

    return Math.floor(Date.now() / 1000) <= expires_at + 300;
  }

  function set_qrcode_status(text, status) {
    qrcode_status.
        removeClass('is-loading is-ready is-expired').
        addClass('is-' + status).
        css('display', 'flex');
    qrcode_countdown.text(text).show();
  }

  function show_qrcode_loading() {
    if (f2f_qrcode.length) {
      f2f_qrcode.removeAttr('src').hide();
    }

    qrcode_expired.hide();
    set_qrcode_status(get_i18n_message('qrcode_loading', 'Generating QR code...'), 'loading');
    qrcode_loading.css('display', 'flex');
  }

  function hide_qrcode_loading() {
    qrcode_loading.hide();

    if (f2f_qrcode.attr('src')) {
      f2f_qrcode.show();
    }
  }

  function load_qrcode_image(image_source, callback) {
    var image_element = f2f_qrcode.get(0);
    var is_complete = false;

    if (!image_element || !image_source) {
      hide_qrcode_loading();
      show_qrcode_failure();
      return;
    }

    function complete_image_load(is_success) {
      if (is_complete) {
        return;
      }

      is_complete = true;
      f2f_qrcode.off('.wprsAlipayQrcode');
      hide_qrcode_loading();

      if (!is_success) {
        show_qrcode_failure();
        return;
      }

      if (typeof callback === 'function') {
        callback();
      }
    }

    show_qrcode_loading();
    f2f_qrcode.
        one('load.wprsAlipayQrcode', function() {
          complete_image_load(true);
        }).
        one('error.wprsAlipayQrcode', function() {
          complete_image_load(false);
        }).
        attr('src', image_source);

    if (image_element.complete) {
      window.setTimeout(function() {
        complete_image_load(image_element.naturalWidth > 0);
      }, 0);
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
    set_qrcode_status(
        get_i18n_message('qrcode_expired', 'The QR code has expired. Please refresh it.'),
        'expired',
    );
    qrcode_expired.show();

    if (countdown_timer) {
      clearInterval(countdown_timer);
      countdown_timer = null;
    }
  }

  function show_qrcode_failure() {
    set_qrcode_status(
        get_i18n_message('qrcode_failure', 'Failed to generate the QR code. Please refresh it.'),
        'expired',
    );
    qrcode_expired.show();

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
    set_qrcode_status(
        get_i18n_message('qrcode_ready', 'Please scan the QR code with Alipay within %s.').
            replace('%s', format_remaining_time(remaining_seconds)),
        'ready',
    );
  }

  function start_qrcode_countdown() {
    if (countdown_timer) {
      clearInterval(countdown_timer);
    }

    update_qrcode_countdown();
    countdown_timer = setInterval(update_qrcode_countdown, 1000);
  }

  if (f2f_qrcode.length) {
    f2f_qrcode.on('error.wprsAlipayQrcode', show_qrcode_failure);

    if (f2f_qrcode.attr('src')) {
      start_qrcode_countdown();

      if (f2f_qrcode.get(0).complete && f2f_qrcode.get(0).naturalWidth === 0) {
        show_qrcode_failure();
      }
    } else {
      show_qrcode_failure();
    }
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
          if (should_continue_query()) {
            query_timer = setTimeout(wprs_woo_alipay_query_order, loop_time);
          }
        }
      },
      error  : function() {
        if (should_continue_query()) {
          query_timer = setTimeout(wprs_woo_alipay_query_order, loop_time);
        }
      },
    });
  };

  /**
   * 支付成功后，如果没有自动跳转，点击按钮查询订单并跳转支付结果
   */
  $('#js-alipay-success, #js-alipay-fail').click(function() {
    var querying_message = $('<div/>').
        text(get_i18n_message('order_querying', 'Checking payment status...')).
        css('padding', '1rem');

    $.blockUI({message: querying_message});

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
        if (data && data.success === true && data.data && data.data.url) {
          location.href = data.data.url;
        } else if (data && data.success === true && data.data && data.data.qrcode_image) {
          confirm_modal.data('expires_at', data.data.expires_at);
          confirm_modal.attr('data-expires_at', data.data.expires_at);

          if (query_timer) {
            clearTimeout(query_timer);
            query_timer = null;
          }

          loop_count = 50;
          qrcode_expired.hide();
          load_qrcode_image(data.data.qrcode_image, function() {
            start_qrcode_countdown();
            wprs_woo_alipay_query_order();
          });
        } else {
          hide_qrcode_loading();
          show_qrcode_failure();
          window.alert(
              data && data.data
                  ? data.data
                  : get_i18n_message('qrcode_refresh_failure', 'Failed to refresh the QR code. Please try again later.'),
          );
        }
      },
      error  : function() {
        hide_qrcode_loading();
        show_qrcode_failure();
        window.alert(
            get_i18n_message('qrcode_refresh_failure', 'Failed to refresh the QR code. Please try again later.'),
        );
      },
      complete: function() {
        button.prop('disabled', false);
      },
    });
  });

  wprs_woo_alipay_query_order();

});
