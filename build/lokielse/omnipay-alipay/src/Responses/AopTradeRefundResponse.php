<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeRefundRequest;

class AopTradeRefundResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_refund_response';

    /**
     * @var AopTradeRefundRequest
     */
    protected $request;
}
