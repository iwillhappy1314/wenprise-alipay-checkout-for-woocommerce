<?php
/**
 * 结账页本地化链路冒烟测试。
 *
 * @package Wenprise_Alipay_Checkout_For_Woocommerce
 */

declare( strict_types=1 );

$plugin_dir      = dirname( __DIR__ );
$init_source     = file_get_contents( $plugin_dir . '/src/Init.php' );
$frontend_source = file_get_contents( $plugin_dir . '/frontend/scripts.js' );
$main_source     = file_get_contents( $plugin_dir . '/wenprise-alipay-checkout-for-woocommerce.php' );

if ( false === $init_source || false === $frontend_source || false === $main_source ) {
	throw new RuntimeException( '无法读取本地化链路源码。' );
}

$loaded_translation_file = '';

/**
 * 记录测试中的 WordPress action，不实际注册 hook。
 */
function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
}

/**
 * 记录测试中的 WordPress filter，不实际注册 hook。
 */
function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
}

/**
 * 返回测试使用的中文 locale。
 */
function determine_locale(): string {
	return 'zh_CN';
}

/**
 * 返回测试所需的插件相对路径。
 */
function plugin_basename( string $file ): string {
	return basename( dirname( $file ) ) . '/' . basename( $file );
}

/**
 * 模拟 WordPress 注册插件语言目录。
 */
function load_plugin_textdomain( $domain, $deprecated = false, $plugin_rel_path = false ): bool {
	return true;
}

/**
 * 模拟卸载已加载的旧翻译。
 */
function unload_textdomain( $domain, $reloadable = false ): bool {
	return true;
}

/**
 * 记录最终明确加载的语言文件。
 */
function load_textdomain( $domain, $mofile, $locale = null ): bool {
	$GLOBALS[ 'loaded_translation_file' ] = $mofile;

	return true;
}

define( 'WENPRISE_ALIPAY_PATH', $plugin_dir . '/' );
define( 'WENPRISE_ALIPAY_FILE_PATH', $plugin_dir . '/wenprise-alipay-checkout-for-woocommerce.php' );
define( 'WENPRISE_ALIPAY_BASE_FILE', 'wenprise-alipay-checkout-for-woocommerce/wenprise-alipay-checkout-for-woocommerce.php' );

require_once $plugin_dir . '/src/Init.php';

$init = new \Wenprise\Alipay\Init();
$init->load_bundled_textdomain();

$expected_translation_file = $plugin_dir . '/languages/wenprise-alipay-checkout-for-woocommerce-zh_CN.mo';

if ( $expected_translation_file !== $loaded_translation_file ) {
	throw new RuntimeException( '没有优先加载插件随包发布的中文翻译。' );
}

$required_translation_keys = [
	'qrcode_loading',
	'qrcode_expired',
	'qrcode_failure',
	'qrcode_ready',
	'order_querying',
	'qrcode_refresh_failure',
];

foreach ( $required_translation_keys as $translation_key ) {
	if ( false === strpos( $init_source, "'{$translation_key}'" ) ) {
		throw new RuntimeException( "缺少前端翻译键：{$translation_key}" );
	}
}

$hardcoded_frontend_messages = [
	'正在生成二维码...',
	'二维码已过期，请刷新。',
	'二维码生成失败，请重新生成。',
	'订单查询中...',
	'二维码重新生成失败，请稍后重试。',
];

foreach ( $hardcoded_frontend_messages as $message ) {
	if ( false !== strpos( $frontend_source, $message ) ) {
		throw new RuntimeException( "前端仍存在硬编码文案：{$message}" );
	}
}

if ( false === strpos( $frontend_source, 'get_i18n_message' ) ) {
	throw new RuntimeException( '前端没有通过统一翻译函数读取文案。' );
}

if ( false === strpos( $init_source, 'load_bundled_textdomain' ) ) {
	throw new RuntimeException( '插件没有显式优先加载随包翻译。' );
}

if ( false !== strpos( $main_source, "load_plugin_textdomain( 'wenprise-alipay-checkout-for-woocommerce'" ) ) {
	throw new RuntimeException( '主插件文件仍在过早注册旧的翻译加载流程。' );
}

echo "Localization smoke test passed.\n";
