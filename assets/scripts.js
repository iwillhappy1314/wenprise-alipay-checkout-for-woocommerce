(function($) {
  var loopCnt = 50;
  var looptime = 500; //ms

  /**
   * 点击提交支付表单
   */
  //$('#alipay-submit-button').click();

  $.blockUI({
    message: $('#js-alipay-confirm-modal'),
    css    : {
      width : '500px',
      height: '300px',
    },
  });

  /**
   * 支付成功后，如果没有自动跳转，点击按钮查询订单并跳转支付结果
   */
  $('#js-alipay-success').click(function() {
    $.blockUI({message: '<h1>订单查询中...</h1>'});

    wprs_woo_alipay_query_order();
  });

  /**
   * 支付失败后，跳转到重新支付页面
   */
  $('#js-alipay-fail').click(function() {
    wprs_woo_alipay_query_order();
    $.unblockUI();
  });

  /**
   * 查询订单支付结果
   */
  function wprs_woo_alipay_query_order() {
    $.ajax({
      type   : 'POST',
      url    : WpWooAlipayData.query_url,
      data   : {
        order_id: $('input[name=order_id]').val(),
      },
      success: function(data) {
        if (data && data.success === true) {
          location.href = data.data;
        } else {
          if (loopCnt-- > 0) {
            setTimeout(wprs_woo_alipay_query_order, looptime);
          }
        }
      },
      error  : function(data) {
        if (loopCnt-- > 0) {
          setTimeout(wprs_woo_alipay_query_order, looptime);
        }
      },
    });
  }

  wprs_woo_alipay_query_order();

  $('#js-wprs-alipay').bind('click', function() {
    wprs_woo_alipay_query_order();
  });

})(jQuery);