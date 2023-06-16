<?php
/**
 * Plugin Name: Wenprise Alipay Payment Gateway For WooCommerce
 * Plugin URI: https://www.wpzhiku.com/wenprise-alipay-payment-gateway-for-woocommerce
 * Description: Alipay Checkout For WooCommerce，WooCommerce 支付宝全功能支付网关
 * Version: 1.3.2
 * Author: WordPress 智库
 * Author URI: https://www.wpzhiku.com
 * Text Domain: wprs-wc-alipay
 * Domain Path: /languages
 * Requires PHP: 7.1
 * Requires at least: 4.7
 * Tested up to: 6.2
 * WC requires at least: 3.6
 * WC tested up to: 7.8
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (PHP_VERSION_ID < 70100) {

    // 显示警告信息
    if (is_admin()) {
        add_action('admin_notices', function ()
        {
            printf('<div class="error"><p>' . __('Wenprise Alipay Payment Gateway For WooCommerce 需要 PHP %1$s 以上版本才能运行，您当前的 PHP 版本为 %2$s， 请升级到 PHP 到 %1$s 或更新的版本， 否则插件没有任何作用。',
                    'wprs') . '</p></div>',
                '7.1.0', PHP_VERSION);
        });
    }

    return;
}

const WENPRISE_ALIPAY_FILE_PATH = __FILE__;
define('WENPRISE_ALIPAY_PATH', plugin_dir_path(__FILE__));
define('WENPRISE_ALIPAY_URL', plugin_dir_url(__FILE__));
const WENPRISE_ALIPAY_VERSION        = '1.1.0';
const WENPRISE_ALIPAY_WOOCOMMERCE_ID = 'wprs-wc-alipay';
const WENPRISE_ALIPAY_ASSETS_URL     = WENPRISE_ALIPAY_URL . 'frontend/';


add_action('wp_enqueue_scripts', function ()
{
    if ( ! class_exists('WC_Payment_Gateway')) {
        return;
    }

    if (is_checkout_pay_page()) {
        wp_enqueue_style('wprs-wc-alipay-style', plugins_url('/frontend/styles.css', __FILE__), [], WENPRISE_ALIPAY_VERSION, false);
        wp_enqueue_script('wprs-wc-alipay-script', plugins_url('/frontend/scripts.js', __FILE__), ['jquery', 'wc-checkout'], WENPRISE_ALIPAY_VERSION, true);
        wp_enqueue_script('qrcode', WC()->plugin_url() . '/assets/js/jquery-qrcode/jquery.qrcode.js', ['jquery'], WENPRISE_ALIPAY_VERSION);

        $js_data = [
            'query_url' => WC()->api_request_url('wprs-wc-query-order'),
        ];

        wp_localize_script('wprs-wc-alipay-script', 'WpWooAlipayData', $js_data);
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


    add_action('admin_enqueue_scripts', function ($hook)
    {
        if (isset($_GET[ 'section' ]) && $_GET[ 'section' ] === 'wprs-wc-alipay') {
            wp_enqueue_script('wprs-wc-alipay-admin-script', plugins_url('/frontend/admin.js', __FILE__), ['jquery']);
        }
    });

}, 0);


add_filter('woocommerce_pay_order_button_html', function ($html)
{
    global $wp;

    $order_id    = $wp->query_vars[ 'order-pay' ];
    $order       = wc_get_order($order_id);
    $payment_url = $order->get_meta('_gateway_payment_url');

    if ($payment_url) {
        $html .= '<input type="hidden" name="wc-alipay-payment-url" value="' . $payment_url . '">';
    }

    return $html;
});


/**
 * 插件插件设置链接
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links)
{
    $url = admin_url('admin.php?page=wc-settings&tab=checkout&section=wprs-wc-alipay');
    $url = '<a href="' . esc_url($url) . '">' . __('Settings', 'wprs-wc-alipay') . '</a>';
    array_unshift($links, $url);

    return $links;
});


add_action('before_woocommerce_init', function ()
{
    if (class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility('custom_order_tables', WENPRISE_ALIPAY_FILE_PATH, true);
    }
});


add_filter('trp_no_translate_selectors', function ($selectors_array, $language)
{
    $selectors_array[] = '.rs-payment-url';

    return $selectors_array;
}, 10, 2);


/**
 * 避免 TranslatePress 插件翻译签名字符串
 */
add_filter('option_trp_advanced_settings', function ($options)
{
    $options[ 'exclude_gettext_strings' ][ 'string' ][] = 'Pay for order %1$s at %2$s';
    $options[ 'exclude_gettext_strings' ][ 'domain' ][] = 'wprs-wc-alipay';

    return $options;
});