<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopCompletePurchaseRequest;

class AopCompleteRefundResponse extends AbstractResponse
{

    /**
     * @var AopCompletePurchaseRequest
     */
    protected $request;

    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return true;
    }

    public function getResponseText()
    {
        if ($this->isSuccessful()) {
            return 'success';
        } else {
            return 'fail';
        }
    }

    public function isRefunded()
    {
        $trade_status = array_get($this->data, 'trade_status');
        if ($trade_status) {
            // 全额退款为 TRADE_CLOSED；非全额退款为 TRADE_SUCCESS
            if ($trade_status == 'TRADE_CLOSED' || $trade_status == 'TRADE_SUCCESS') {
                return true;
            } else {
                return false;
            }
        } elseif (array_get($this->data, 'code') == '10000') {
            return true;
        }
        return false;
    }
}
