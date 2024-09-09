<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTransferToAccountRequest;

class AopTransferToAccountResponse extends AbstractAopResponse
{
    protected $key = 'alipay_fund_trans_toaccount_transfer_response';

    /**
     * @var AopTransferToAccountRequest
     */
    protected $request;
}
