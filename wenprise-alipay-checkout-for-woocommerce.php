<?php
/**
 * Plugin Name: Wenprise Alipay Payment Gateway For WooCommerce
 * Plugin URI: https://www.wpzhiku.com/wenprise-alipay-payment-gateway-for-woocommerce
 * Description: Alipay Checkout For WooCommerce，WooCommerce 支付宝全功能支付网关
 * Version: 2.0.1
 * Author: WordPress智库
 * Author URI: https://www.wpzhiku.com
 * Text Domain: wprs-wc-alipay
 * Domain Path: /languages
 * Requires PHP: 7.2
 * Requires at least: 4.7
 * Tested up to: 6.2
 * WC requires at least: 3.6
 * WC tested up to: 8.5
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( PHP_VERSION_ID < 70200 ) {

	// 显示警告信息
	if ( is_admin() ) {
		add_action( 'admin_notices', function ()
		{
			printf( '<div class="error"><p>' . __( 'Wenprise Alipay Payment Gateway For WooCommerce 需要 PHP %1$s 以上版本才能运行，您当前的 PHP 版本为 %2$s， 请升级到 PHP 到 %1$s 或更新的版本， 否则插件没有任何作用。',
					'wprs' ) . '</p></div>',
				'7.2.0', PHP_VERSION );
		} );
	}

	return;
}

define( 'WENPRISE_ALIPAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'WENPRISE_ALIPAY_URL', plugin_dir_url( __FILE__ ) );
define( 'WENPRISE_ALIPAY_BASE_FILE', plugin_basename( __FILE__ ) );
const WENPRISE_ALIPAY_FILE_PATH      = __FILE__;
const WENPRISE_ALIPAY_VERSION        = '2.0.1';
const WENPRISE_ALIPAY_WOOCOMMERCE_ID = 'wprs-wc-alipay';
const WENPRISE_ALIPAY_ASSETS_URL     = WENPRISE_ALIPAY_URL . 'frontend/';


add_action( 'plugins_loaded', function ()
{

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	// 加载文件
	require WENPRISE_ALIPAY_PATH . 'vendor/autoload.php';

	// 加载语言包
	load_plugin_textdomain( 'wprs-wc-alipay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// 添加支付方法
	add_filter( 'woocommerce_payment_gateways', function ( $methods )
	{
		$methods[] = 'Wenprise\\Alipay\\PaymentGateway';

		return $methods;
	} );

	new \Wenprise\Alipay\Init();
}, 0 );