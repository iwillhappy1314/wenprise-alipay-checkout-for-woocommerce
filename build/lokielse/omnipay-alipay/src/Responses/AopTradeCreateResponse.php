<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeCreateRequest;

class AopTradeCreateResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_create_response';

    /**
     * @var AopTradeCreateRequest
     */
    protected $request;


    public function getTradeNo()
    {
        return $this->getAlipayResponse('trade_no');
    }


    public function getOutTradeNo()
    {
        return $this->getAlipayResponse('out_trade_no');
    }
}
