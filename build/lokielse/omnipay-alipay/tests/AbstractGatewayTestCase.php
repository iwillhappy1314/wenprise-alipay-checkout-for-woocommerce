<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Tests;

use Wenprise\Alipay\Omnipay\Tests\GatewayTestCase;

abstract class AbstractGatewayTestCase extends GatewayTestCase
{
    protected $partner = ALIPAY_PARTNER;

    protected $key = ALIPAY_KEY;

    protected $sellerId = ALIPAY_SELLER_ID;

    protected $appId = ALIPAY_APP_ID;

    protected $appPrivateKey = ALIPAY_APP_PRIVATE_KEY;

    protected $alipayPublicKey = ALIPAY_PUBLIC_KEY;

    protected $appEncryptKey = ALIPAY_APP_ENCRYPT_KEY;


    protected function setUp()
    {
        parent::setUp();
    }
}
