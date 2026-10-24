<?php
/**
 * 后端二维码生成器冒烟测试。
 *
 * @package Wenprise_Alipay_Checkout_For_Woocommerce
 */

declare( strict_types=1 );

use Wenprise\Alipay\Support\QrcodeGenerator;

require dirname( __DIR__ ) . '/vendor/autoload.php';

$generator   = new QrcodeGenerator();
$qrcode_url  = 'https://qr.alipay.com/example-payment-code';
$data_uri    = $generator->generate_data_uri( $qrcode_url );
$data_prefix = 'data:image/svg+xml;base64,';

if ( 0 !== strpos( $data_uri, $data_prefix ) ) {
	throw new RuntimeException( '二维码生成结果不是 SVG Data URI。' );
}

$svg = base64_decode( substr( $data_uri, strlen( $data_prefix ) ), true );

if ( false === $svg || false === strpos( $svg, '<svg' ) ) {
	throw new RuntimeException( '二维码 Data URI 无法解码为 SVG。' );
}

try {
	$generator->generate_data_uri( '   ' );
	throw new RuntimeException( '空二维码内容没有触发异常。' );
} catch ( InvalidArgumentException $exception ) {
	// 预期行为：空二维码内容必须立即失败。
}

echo "QR code generator smoke test passed.\n";
