(function($) {
  var loopCnt = 50;
  var looptime = 1000; //ms

  /**
   * 点击提交支付表单
   */
  $('#alipay-submit-button').click();

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
  $('#js-alipay-success, #js-alipay-fail').click(function() {
    $.blockUI({message: '<div style="padding: 1rem;">订单查询中...</div>'});

    wprs_woo_alipay_query_order(true);
  });


  /**
   * 查询订单支付结果
   * @param manual 是否手动检查，手动检查时，无论拍支付是否成功，均需要跳转页面
   */
  function wprs_woo_alipay_query_order(manual = false) {
    $.ajax({
      type   : 'POST',
      url    : WpWooAlipayData.query_url,
      data   : {
        order_id: $('input[name=order_id]').val(),
      },
      success: function(data) {
        if (data && data.success === true || manual === true) {
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

})(jQuery);