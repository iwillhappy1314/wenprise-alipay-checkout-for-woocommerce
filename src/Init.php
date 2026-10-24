<?php

namespace Wenprise\Alipay;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * 注册插件的 WooCommerce 集成、前端资源和管理端 hooks。
 */
class Init {

	/**
	 * 初始化插件 hooks。
	 */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'load_bundled_textdomain' ], 0 );
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
	 * 优先加载插件随包发布的当前语言翻译，避免全局旧语言包覆盖新文案。
	 *
	 * @return void
	 */
	function load_bundled_textdomain() {
		$text_domain   = 'wenprise-alipay-checkout-for-woocommerce';
		$language_path = WENPRISE_ALIPAY_PATH . 'languages';
		$locale        = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		$translation   = $language_path . '/' . $text_domain . '-' . $locale . '.mo';

		load_plugin_textdomain( $text_domain, false, dirname( plugin_basename( WENPRISE_ALIPAY_FILE_PATH ) ) . '/languages' );

		if ( ! is_readable( $translation ) ) {
			return;
		}

		unload_textdomain( $text_domain );
		load_textdomain( $text_domain, $translation );
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


	/**
	 * 声明 WooCommerce 高性能订单存储兼容性。
	 *
	 * @return void
	 */
	function add_custom_table_support() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', WENPRISE_ALIPAY_FILE_PATH );
		}
	}


	/**
	 * 加载结账支付页所需的前端资源和接口配置。
	 *
	 * @return void
	 */
	function enqueue_scripts() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		if ( is_checkout_pay_page() ) {
			wp_enqueue_style( 'wprs-wc-alipay-style', WENPRISE_ALIPAY_URL . '/frontend/styles.css', [], WENPRISE_ALIPAY_VERSION, false );
			wp_enqueue_script( 'wprs-wc-alipay-script', WENPRISE_ALIPAY_URL . '/frontend/scripts.js', [ 'jquery', 'wc-checkout' ], WENPRISE_ALIPAY_VERSION, true );

			$js_data = [
				'query_url'   => WC()->api_request_url( 'wprs-wc-query-order' ),
				'refresh_url' => WC()->api_request_url( 'wprs-wc-refresh-qrcode' ),
				'nonce'       => wp_create_nonce( 'wprs_wc_alipay_order_action' ),
				'i18n'        => [
					'qrcode_loading'         => __( 'Generating QR code...', 'wenprise-alipay-checkout-for-woocommerce' ),
					'qrcode_expired'         => __( 'The QR code has expired. Please refresh it.', 'wenprise-alipay-checkout-for-woocommerce' ),
					'qrcode_failure'         => __( 'Failed to generate the QR code. Please refresh it.', 'wenprise-alipay-checkout-for-woocommerce' ),
					'qrcode_ready'           => __( 'Please scan the QR code with Alipay within %s.', 'wenprise-alipay-checkout-for-woocommerce' ),
					'order_querying'         => __( 'Checking payment status...', 'wenprise-alipay-checkout-for-woocommerce' ),
					'qrcode_refresh_failure' => __( 'Failed to refresh the QR code. Please try again later.', 'wenprise-alipay-checkout-for-woocommerce' ),
				],
			];

			wp_localize_script( 'wprs-wc-alipay-script', 'WpWooAlipayData', $js_data );
		}
	}


	/**
	 * 加载支付网关设置页脚本。
	 *
	 * @return void
	 */
	function admin_enqueue_scripts() {
		$section = isset( $_GET[ 'section' ] ) ? sanitize_key( wp_unslash( $_GET[ 'section' ] ) ) : '';

		if ( $section === WENPRISE_ALIPAY_WOOCOMMERCE_ID ) {
			wp_enqueue_script( 'wprs-wc-alipay-admin-script', WENPRISE_ALIPAY_URL . '/frontend/admin.js', [ 'jquery' ] );
		}
	}


	/**
	 * 为订单支付按钮附加支付宝跳转地址。
	 *
	 * @param string $html 支付按钮 HTML。
	 *
	 * @return string
	 */
	function modify_pay_button_html( $html ) {
		global $wp;

		$order_id    = $wp->query_vars[ 'order-pay' ];
		$order       = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			return $html;
		}

		$payment_url = $order->get_meta( '_gateway_payment_url' );

		if ( $payment_url ) {
			$html .= '<input type="hidden" name="wc-alipay-payment-url" value="' . esc_url( $payment_url ) . '">';
		}

		return $html;
	}

	/**
	 * 避免 TranslatePress 插件翻译签名字符串
	 */
	function ignore_translate_strings( $options ) {
		$options[ 'exclude_gettext_strings' ][ 'string' ][] = 'Pay for order %1$s at %2$s';
		$options[ 'exclude_gettext_strings' ][ 'domain' ][] = 'wenprise-alipay-checkout-for-woocommerce';

		return $options;
	}


	/**
	 * 将支付跳转元素加入 TranslatePress 排除选择器。
	 *
	 * @param array  $selectors_array 已有排除选择器。
	 * @param string $language        当前语言。
	 *
	 * @return array
	 */
	function ignore_translate_elements( $selectors_array, $language ) {
		$selectors_array[] = '.rs-payment-url';

		return $selectors_array;
	}


	/**
	 * 插件插件设置链接
	 */
	function add_settings_link( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . WENPRISE_ALIPAY_WOOCOMMERCE_ID );
		$url = '<a href="' . esc_url( $url ) . '">' . __( 'Settings', 'wenprise-alipay-checkout-for-woocommerce' ) . '</a>';
		array_unshift( $links, $url );

		return $links;
	}

}
