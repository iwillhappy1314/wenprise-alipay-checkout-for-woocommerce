<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Tests;

use Wenprise\Alipay\Omnipay\Alipay\AopJsGateway;
use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeCreateResponse;

class AopJsGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var AopJsGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new AopJsGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey(ALIPAY_AOP_PRIVATE_KEY);
    }


    public function testPurchase()
    {
        $this->setMockHttpResponse('AopJs_Purchase_Failure.txt');

        /**
         * @var AopTradeCreateResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'subject'      => 'test',
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'total_amount' => '0.01',
                    'product_code' => '0.01',
                ]
            ]
        )->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertArrayHasKey('code', $response->getAlipayResponse());
    }
}
