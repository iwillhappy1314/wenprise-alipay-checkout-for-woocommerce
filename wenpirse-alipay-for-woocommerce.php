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

if (version_compare(phpversion(), '7.1.3', '<')) {

    // 显示警告信息
    if (is_admin()) {
        add_action('admin_notices', function ()
        {
            printf('<div class="error"><p>' . __('Wenprise Alipay Checkout For WooCommerce 需要 PHP %1$s 以上版本才能运行，您当前的 PHP 版本为 %2$s， 请升级到 PHP 到 %1$s 或更新的版本， 否则插件没有任何作用。',
                    'wprs') . '</p></div>',
                '7.1.3', phpversion());
        });
    }

    return;
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

    Puc_v4_Factory::buildUpdateChecker(
        'https://api.wpcio.com/api/plugin/info/wenprise-alipay-for-woocommerce',
        __FILE__,
        'wenprise-alipay-for-woocommerce'
    );

}, 0);


