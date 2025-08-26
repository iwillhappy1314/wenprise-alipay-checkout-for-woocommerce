<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeRefundQueryRequest;

class AopTradeRefundQueryResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_fastpay_refund_query_response';

    /**
     * @var AopTradeRefundQueryRequest
     */
    protected $request;
}
