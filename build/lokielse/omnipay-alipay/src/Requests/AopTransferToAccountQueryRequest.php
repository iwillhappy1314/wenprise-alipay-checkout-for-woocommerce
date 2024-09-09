<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTransferToAccountQueryResponse;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTransferToAccountQueryRequest
 *
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://docs.open.alipay.com/api_28/alipay.fund.trans.order.query
 */
class AopTransferToAccountQueryRequest extends AbstractAopRequest
{
    protected $method = 'alipay.fund.trans.order.query';

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTransferToAccountQueryResponse($this, $data);
    }

    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContentOne(
            'out_biz_no',
            'order_id'
        );
    }
}
