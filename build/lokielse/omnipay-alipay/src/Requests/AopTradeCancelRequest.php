<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeCancelResponse;
use Wenprise\Alipay\Omnipay\Common\Exception\InvalidRequestException;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradeCancelRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/doc2/apiDetail.htm?apiId=866&docType=4
 */
class AopTradeCancelRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.cancel';


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return ResponseInterface
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTradeCancelResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContentOne(
            'out_trade_no',
            'trade_no'
        );
    }
}
