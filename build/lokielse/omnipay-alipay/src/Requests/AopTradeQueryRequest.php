<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeQueryResponse;
use Wenprise\Alipay\Omnipay\Common\Exception\InvalidRequestException;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradeQueryRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=757
 */
class AopTradeQueryRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.query';


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

        return $this->response = new AopTradeQueryResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContentOne(
            'trade_no',
            'out_trade_no'
        );
    }
}
