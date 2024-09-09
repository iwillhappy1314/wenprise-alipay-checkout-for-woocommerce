<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradePreCreateRequest;

class AopTradePreCreateResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_precreate_response';

    /**
     * @var AopTradePreCreateRequest
     */
    protected $request;


    public function getQrCode()
    {
        return $this->getAlipayResponse('qr_code');
    }
}
