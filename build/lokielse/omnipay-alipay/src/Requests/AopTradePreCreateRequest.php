<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradePreCreateResponse;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradePreCreateRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=862
 */
class AopTradePreCreateRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.precreate';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     * @throws \Wenprise\Alipay\Psr\Http\Client\Exception\NetworkException
     * @throws \Wenprise\Alipay\Psr\Http\Client\Exception\RequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTradePreCreateResponse($this, $data);
    }


    /**
     * @throws \Wenprise\Alipay\Omnipay\Common\Exception\InvalidRequestException
     */
    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent(
            'out_trade_no',
            'total_amount',
            'subject'
        );
    }
}
