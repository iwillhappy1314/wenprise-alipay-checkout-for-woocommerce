<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeRefundRequest;

class AopTradeCancelResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_cancel_response';

    /**
     * @var AopTradeRefundRequest
     */
    protected $request;
}
