jQuery(document).ready(function($){
    function wprs_woo_alipay_check_select() {
        var check_el = $('select[name=woocommerce_wprs-wc-alipay_cert_type]').val(),
            condition_el = $(
                'input[name=woocommerce_wprs-wc-alipay_alipay_cert_public_key_rsa2], input[name=woocommerce_wprs-wc-alipay_alipay_root_cert], input[name=woocommerce_wprs-wc-alipay_app_cert_publicKey]').
                parents('tr');

        condition_el2 = $('textarea[name=woocommerce_wprs-wc-alipay_alipay_public_key]').parents('tr');

        if (check_el === 'public_key_certificate') {
            condition_el.show();
            condition_el2.hide();
        } else {
            condition_el.hide();
            condition_el2.show();
        }
    }

    wprs_woo_alipay_check_select();

    $('select[name="woocommerce_wprs-wc-alipay_cert_type"]').change(function() {
        wprs_woo_alipay_check_select();
    });
});