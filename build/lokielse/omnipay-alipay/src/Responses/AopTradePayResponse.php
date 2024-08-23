<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradePayRequest;

class AopTradePayResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_pay_response';

    /**
     * @var AopTradePayRequest
     */
    protected $request;


    public function isPayFailed()
    {
        return $this->getCode() == '40004';
    }


    public function isPaid()
    {
        return $this->getCode() == '10000';
    }


    public function isWaitPay()
    {
        return $this->getCode() == '10003';
    }


    public function isUnknownException()
    {
        return $this->getCode() == '20000';
    }
}
