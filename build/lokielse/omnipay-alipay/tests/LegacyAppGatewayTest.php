<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Tests;

use Wenprise\Alipay\Omnipay\Alipay\Common\Signer;
use Wenprise\Alipay\Omnipay\Alipay\LegacyAppGateway;
use Wenprise\Alipay\Omnipay\Alipay\Responses\LegacyAppPurchaseResponse;
use Wenprise\Alipay\Omnipay\Alipay\Responses\LegacyCompletePurchaseResponse;

class LegacyAppGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var LegacyAppGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();

        $this->gateway = new LegacyAppGateway($this->getHttpClient(), $this->getHttpRequest());
    }


    public function testCreateOrder()
    {
        $partner    = '123456789';
        $privateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';

        //$partner    = ALIPAY_PARTNER;
        //$privateKey = ALIPAY_ASSET_DIR . '/dist/common/rsa_private_key.pem';

        $this->assertFileExists($privateKey);

        $this->gateway = new LegacyAppGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($partner);
        $this->gateway->setSellerId($partner);
        $this->gateway->setPrivateKey($privateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');

        $this->options = [
            'out_trade_no' => '2014010122390001',
            //'out_trade_no' => date('YmdHis').mt_rand(1000,9999),
            'subject'      => 'test',
            'total_fee'    => '0.01',
        ];

        /**
         * @var LegacyAppPurchaseResponse $response
         */
        $response = $this->gateway->purchase($this->options)->send();
        $this->assertEquals('e16fdd8098c197201986cd9c3a8fb276', md5($response->getOrderString()));
    }
}
