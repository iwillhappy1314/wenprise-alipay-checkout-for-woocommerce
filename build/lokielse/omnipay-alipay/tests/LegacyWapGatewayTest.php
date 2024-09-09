<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Tests;

use Wenprise\Alipay\Omnipay\Alipay\Common\Signer;
use Wenprise\Alipay\Omnipay\Alipay\LegacyWapGateway;
use Wenprise\Alipay\Omnipay\Alipay\Requests\LegacyCompletePurchaseRequest;
use Wenprise\Alipay\Omnipay\Alipay\Responses\LegacyCompletePurchaseResponse;
use Wenprise\Alipay\Omnipay\Alipay\Responses\LegacyWapPurchaseResponse;

class LegacyWapGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var LegacyWapGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new LegacyWapGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setPartner($this->partner);
        $this->gateway->setKey($this->key);
        $this->gateway->setSellerId($this->sellerId);
        $this->gateway->setNotifyUrl('https://www.example.com/notify');
        $this->gateway->setReturnUrl('https://www.example.com/return');
        $this->options = [
            'out_trade_no' => '2014010122390001',
            'subject'      => 'test',
            'total_fee'    => '0.01',
            'show_url'     => 'https://www.example.com/item/123456',
        ];
    }


    public function testPurchase()
    {
        /**
         * @var LegacyWapPurchaseResponse $response
         */
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isSuccessful());
        $this->assertNotEmpty($response->getRedirectUrl());
    }
}
