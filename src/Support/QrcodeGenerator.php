<?php

namespace Wenprise\Alipay\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * 将支付宝付款地址转换为可直接显示的 SVG 二维码。
 */
class QrcodeGenerator {

	/**
	 * 生成包含规范留白的 SVG Data URI。
	 *
	 * @param string $content 支付宝返回的二维码内容。
	 *
	 * @return string
	 */
	public function generate_data_uri( string $content ): string {
		if ( '' === trim( $content ) ) {
			throw new \InvalidArgumentException( '二维码内容不能为空。' );
		}

		$options = new QROptions(
			[
				'outputType'    => QROutputInterface::MARKUP_SVG,
				'outputBase64'  => true,
				'eccLevel'      => EccLevel::M,
				'addQuietzone'  => true,
				'quietzoneSize' => 4,
			]
		);

		return ( new QRCode( $options ) )->render( $content );
	}
}
