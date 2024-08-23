<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTransferToAccountQueryRequest;

class AopTransferToAccountQueryResponse extends AbstractAopResponse
{
    protected $key = 'alipay_fund_trans_order_query_response';

    /**
     * @var AopTransferToAccountQueryRequest
     */
    protected $request;
}
