<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Tests;

use Wenprise\Alipay\Omnipay\Alipay\AopWapGateway;
use Wenprise\Alipay\Omnipay\Alipay\Common\Signer;
use Wenprise\Alipay\Omnipay\Alipay\Responses\AopCompletePurchaseResponse;
use Wenprise\Alipay\Omnipay\Alipay\Responses\AopCompleteRefundResponse;
use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeWapPayResponse;

class AopWapGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var AopWapGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new AopWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey($this->appPrivateKey);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');
    }


    public function testPurchase()
    {
        /**
         * @var AopTradeWapPayResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'total_amount' => 0.01,
                    'subject'      => 'test',
                    'product_code' => 'QUICK_MSECURITY_PAY',
                ]
            ]
        )->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
    }
}
