<?php
/**
 * Plugin Name: Wenprise Alipay Payment Gateway For WooCommerce
 * Plugin URI: https://www.wpzhiku.com/wenprise-alipay-payment-gateway-for-woocommerce
 * Description: Alipay Checkout For WooCommerce，WooCommerce 支付宝全功能支付网关
 * Version: 1.0.5
 * Author: WordPress 智库
 * Author URI: https://www.wpzhiku.com
 * Text Domain: wprs-wc-alipay
 * Domain Path: /languages
 */

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (version_compare(phpversion(), '5.6.0', '<')) {

    // 显示警告信息
    if (is_admin()) {
        add_action('admin_notices', function ()
        {
            printf('<div class="error"><p>' . __('Wenprise Alipay Payment Gateway For WooCommerce 需要 PHP %1$s 以上版本才能运行，您当前的 PHP 版本为 %2$s， 请升级到 PHP 到 %1$s 或更新的版本， 否则插件没有任何作用。',
                    'wprs') . '</p></div>',
                '5.6.0', phpversion());
        });
    }

    return;
}

define('WENPRISE_ALIPAY_FILE_PATH', __FILE__);
define('WENPRISE_ALIPAY_PATH', plugin_dir_path(__FILE__));
define('WENPRISE_ALIPAY_URL', plugin_dir_url(__FILE__));
define('WENPRISE_ALIPAY_WOOCOMMERCE_ID', 'wprs-wc-alipay');
define('WENPRISE_ALIPAY_ASSETS_URL', WENPRISE_ALIPAY_URL . 'frontend/');


add_action('wp_enqueue_scripts', function ()
{
    if (is_checkout() || is_checkout_pay_page()) {
        wp_enqueue_style('wprs-wc-alipay-style', plugins_url('/frontend/styles.css', __FILE__), [], null, false);
        wp_enqueue_script('wprs-wc-alipay-script', plugins_url('/frontend/scripts.js', __FILE__), ['jquery', 'jquery-blockui'], null, true);

        wp_localize_script('wprs-wc-alipay-script', 'WpWooAlipayData', [
            'query_url'  => WC()->api_request_url('wprs-wc-query-order'),
        ]);
    }
});

add_action('plugins_loaded', function ()
{

    if ( ! class_exists('WC_Payment_Gateway')) {
        return;
    }

    // 加载文件
    require WENPRISE_ALIPAY_PATH . 'vendor/autoload.php';
    require WENPRISE_ALIPAY_PATH . 'helpers.php';
    require WENPRISE_ALIPAY_PATH . 'class-checkout.php';

    // 加载语言包
    load_plugin_textdomain('wprs-wc-alipay', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // 添加支付方法
    add_filter('woocommerce_payment_gateways', function ($methods)
    {
        $methods[] = 'Wenprise_Alipay_Gateway';

        return $methods;
    });

}, 0);


