<?php

namespace Wenprise\Alipay;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

class Init {

	public function __construct() {
		add_action( 'woocommerce_blocks_loaded', [ $this, 'add_block_support' ] );
		add_action( 'before_woocommerce_init', [ $this, 'add_custom_table_support' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_filter( 'option_trp_advanced_settings', [ $this, 'ignore_translate_strings' ] );
		add_filter( 'trp_no_translate_selectors', [ $this, 'ignore_translate_elements' ], 10, 2 );

		add_filter( 'woocommerce_pay_order_button_html', [ $this, 'modify_pay_button_html' ] );
		add_filter( 'plugin_action_links_' . WENPRISE_ALIPAY_BASE_FILE, [ $this, 'add_settings_link' ] );
	}


	/**
	 * Registers WooCommerce Blocks integration.
	 */
	function add_block_support() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				static function ( PaymentMethodRegistry $payment_method_registry )
				{
					$payment_method_registry->register( new BlockSupport() );
				}
			);
		}
	}


	function add_custom_table_support() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', WENPRISE_ALIPAY_FILE_PATH );
		}
	}


	function enqueue_scripts() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		if ( is_checkout_pay_page() ) {
			wp_enqueue_style( 'wprs-wc-alipay-style', WENPRISE_ALIPAY_URL . '/frontend/styles.css', [], WENPRISE_ALIPAY_VERSION, false );
			wp_enqueue_script( 'wprs-wc-alipay-script', WENPRISE_ALIPAY_URL . '/frontend/scripts.js', [ 'jquery', 'wc-checkout' ], WENPRISE_ALIPAY_VERSION, true );
			wp_enqueue_script( 'qrcode', WC()->plugin_url() . '/assets/js/jquery-qrcode/jquery.qrcode.js', [ 'jquery' ], WENPRISE_ALIPAY_VERSION );

			$js_data = [
				'query_url' => WC()->api_request_url( 'wprs-wc-query-order' ),
			];

			wp_localize_script( 'wprs-wc-alipay-script', 'WpWooAlipayData', $js_data );
		}
	}


	function admin_enqueue_scripts() {
		if ( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] === 'wprs-wc-alipay' ) {
			wp_enqueue_script( 'wprs-wc-alipay-admin-script', WENPRISE_ALIPAY_URL . '/frontend/admin.js', [ 'jquery' ] );
		}
	}


	function modify_pay_button_html( $html ) {
		global $wp;

		$order_id    = $wp->query_vars[ 'order-pay' ];
		$order       = wc_get_order( $order_id );
		$payment_url = $order->get_meta( '_gateway_payment_url' );

		if ( $payment_url ) {
			$html .= '<input type="hidden" name="wc-alipay-payment-url" value="' . $payment_url . '">';
		}

		return $html;
	}

	/**
	 * 避免 TranslatePress 插件翻译签名字符串
	 */
	function ignore_translate_strings( $options ) {
		$options[ 'exclude_gettext_strings' ][ 'string' ][] = 'Pay for order %1$s at %2$s';
		$options[ 'exclude_gettext_strings' ][ 'domain' ][] = 'wprs-wc-alipay';

		return $options;
	}


	function ignore_translate_elements( $selectors_array, $language ) {
		$selectors_array[] = '.rs-payment-url';

		return $selectors_array;
	}


	/**
	 * 插件插件设置链接
	 */
	function add_settings_link( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wprs-wc-alipay' );
		$url = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'wprs-wc-alipay' ) . '</a>';
		array_unshift( $links, $url );

		return $links;
	}

}