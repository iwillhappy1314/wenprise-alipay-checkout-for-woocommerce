<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeRefundRequest;

class AopTradeCloseResponse extends AbstractAopResponse
{
    protected $key = 'alipay_trade_close_response';

    /**
     * @var AopTradeRefundRequest
     */
    protected $request;
}
