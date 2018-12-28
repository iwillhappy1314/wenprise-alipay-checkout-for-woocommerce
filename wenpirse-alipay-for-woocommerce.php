<?php
/**
 * Plugin Name: Wenprise Alipay Checkout For WooCommerce
 * Plugin URI: https://www.wpzhiku.com
 * Description: Alipay Checkout For WooCommerce
 * Version: 1.0.1
 * Author: WenPrise Co., Ltd
 * Author URI: https://www.wpzhiku.com
 * Text Domain: wprs-wc-alipay
 * Domain Path: /languages
 */

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WENPRISE_ALIPAY_FILE_PATH', __FILE__);
define('WENPRISE_ALIPAY_PATH', plugin_dir_path(__FILE__));
define('WENPRISE_ALIPAY_URL', plugin_dir_url(__FILE__));
define('WENPRISE_ALIPAY_WOOCOMMERCE_ID', 'wprs-wc-alipay');
define('WENPRISE_ALIPAY_ASSETS_URL', WENPRISE_ALIPAY_URL . 'assets/');

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


Puc_v4_Factory::buildUpdateChecker(
    'https://api.wpcio.com/api/plugin/info/wenprise-alipay-for-woocommerce',
    __FILE__,
    'wenprise-alipay-for-woocommerce'
);