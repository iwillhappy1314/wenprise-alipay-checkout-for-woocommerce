<?php
/**
 * 支付宝 V2 SDK 独立冒烟测试。
 *
 * @package Wenprise_Alipay_Checkout_For_Woocommerce
 */

declare(strict_types=1);

set_error_handler(
	static function (int $severity, string $message, string $file, int $line): bool {
		if (!(error_reporting() & $severity)) {
			return false;
		}

		throw new ErrorException($message, 0, $severity, $file, $line);
	}
);

require_once dirname(__DIR__) . '/includes/aop/AopClient.php';
require_once dirname(__DIR__) . '/includes/aop/AopCertClient.php';

$request_files = glob(dirname(__DIR__) . '/includes/aop/request/*.php');

foreach ($request_files as $request_file) {
	require_once $request_file;
}

/**
 * 验证插件内精简版支付宝 V2 SDK 的版本和关键调用契约。
 */
final class AlipaySdkSmokeTest
{
	private const EXPECTED_SDK_VERSION = 'alipay-sdk-PHP-4.20.1435.ALL';

	private int $assertion_count = 0;

	/**
	 * 执行全部 SDK 冒烟测试。
	 */
	public function run(): void
	{
		$this->test_sdk_version();
		$this->test_client_configuration();
		$this->test_request_contracts();
		$this->test_rsa2_signature_round_trip();

		fwrite(STDOUT, "Alipay SDK smoke tests passed ({$this->assertion_count} assertions).\n");
	}

	/**
	 * 验证普通客户端和证书客户端都报告官方最新 V2 SDK 版本。
	 */
	private function test_sdk_version(): void
	{
		$this->assert_same(self::EXPECTED_SDK_VERSION, $this->get_sdk_version(new AopClient()), 'AopClient SDK version');
		$this->assert_same(self::EXPECTED_SDK_VERSION, $this->get_sdk_version(new AopCertClient()), 'AopCertClient SDK version');
	}

	/**
	 * 验证插件使用的配置可以正确传入普通客户端。
	 */
	private function test_client_configuration(): void
	{
		$config = new AlipayConfig();
		$config->setServerUrl('https://openapi.alipay.com/gateway.do');
		$config->setAppId('test_app_id');
		$config->setPrivateKey('test_private_key');
		$config->setAlipayPublicKey('test_public_key');
		$config->setSignType('RSA2');
		$config->setCharset('UTF-8');
		$config->setFormat('json');

		$client = new AopClient($config);

		$this->assert_same('test_app_id', $client->appId, 'configured app ID');
		$this->assert_same('RSA2', $client->signType, 'configured sign type');
		$this->assert_same('https://openapi.alipay.com/gateway.do', $client->gatewayUrl, 'configured gateway URL');
	}

	/**
	 * 验证插件依赖的请求类仍映射到正确的支付宝接口。
	 */
	private function test_request_contracts(): void
	{
		$request_methods = [
			AlipayTradePagePayRequest::class            => 'alipay.trade.page.pay',
			AlipayTradeWapPayRequest::class             => 'alipay.trade.wap.pay',
			AlipayTradePrecreateRequest::class           => 'alipay.trade.precreate',
			AlipayTradeQueryRequest::class               => 'alipay.trade.query',
			AlipayTradeRefundRequest::class              => 'alipay.trade.refund',
			AlipayTradeFastpayRefundQueryRequest::class => 'alipay.trade.fastpay.refund.query',
			AlipayTradeCloseRequest::class               => 'alipay.trade.close',
		];

		foreach ($request_methods as $request_class => $expected_method) {
			$request = new $request_class();
			$request->setBizContent('{"out_trade_no":"test_order"}');

			$this->assert_same($expected_method, $request->getApiMethodName(), $request_class . ' method');
			$this->assert_same('{"out_trade_no":"test_order"}', $request->getApiParas()['biz_content'] ?? null, $request_class . ' biz_content');
			$this->assert_same('1.0', $request->getApiVersion(), $request_class . ' API version');
		}
	}

	/**
	 * 验证最新版客户端仍能完成 RSA2 签名和验签。
	 */
	private function test_rsa2_signature_round_trip(): void
	{
		$key_pair = openssl_pkey_new([
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);

		$this->assert_true(false !== $key_pair, 'RSA key pair generation');

		$private_key = '';
		openssl_pkey_export($key_pair, $private_key);
		$key_details = openssl_pkey_get_details($key_pair);
		$public_key = $key_details['key'] ?? '';
		$public_key_content = preg_replace('/-----[^-]+-----|\s+/', '', $public_key);
		$private_key_file = tempnam(sys_get_temp_dir(), 'alipay-private-key-');

		$this->assert_true(false !== $private_key_file, 'temporary private key file');
		file_put_contents($private_key_file, $private_key);

		$config = new AlipayConfig();
		$config->setAlipayPublicKey($public_key_content);
		$client = new AopClient($config);
		$client->rsaPrivateKeyFilePath = $private_key_file;
		$params = [
			'app_id' => 'test_app_id',
			'out_trade_no' => 'test_order',
		];
		$signature = $client->rsaSign($params, 'RSA2');
		$verified_params = $params;
		$verified_params['sign'] = $signature;
		$verified_params['sign_type'] = 'RSA2';

		$this->assert_true($client->rsaCheckV1($verified_params, '', 'RSA2'), 'RSA2 signature verification');

		unlink($private_key_file);
	}

	/**
	 * 读取 SDK 客户端内部版本标识。
	 *
	 * @param object $client SDK 客户端。
	 */
	private function get_sdk_version(object $client): string
	{
		$reflection = new ReflectionProperty($client, 'alipaySdkVersion');

		if (PHP_VERSION_ID < 80100) {
			$reflection->setAccessible(true);
		}

		return (string) $reflection->getValue($client);
	}

	/**
	 * 断言两个值完全相同。
	 *
	 * @param mixed  $expected 期望值。
	 * @param mixed  $actual   实际值。
	 * @param string $message  断言说明。
	 */
	private function assert_same($expected, $actual, string $message): void
	{
		++$this->assertion_count;

		if ($expected !== $actual) {
			throw new RuntimeException(sprintf('%s: expected %s, got %s', $message, var_export($expected, true), var_export($actual, true)));
		}
	}

	/**
	 * 断言条件为真。
	 *
	 * @param bool   $condition 条件。
	 * @param string $message   断言说明。
	 */
	private function assert_true(bool $condition, string $message): void
	{
		++$this->assertion_count;

		if (!$condition) {
			throw new RuntimeException($message);
		}
	}
}

(new AlipaySdkSmokeTest())->run();
