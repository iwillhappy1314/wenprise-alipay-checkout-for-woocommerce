<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeRefundResponse;
use Wenprise\Alipay\Omnipay\Common\Exception\InvalidRequestException;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class AopTradeRefundRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://doc.open.alipay.com/docs/api.htm?docType=4&apiId=759
 */
class AopTradeRefundRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.refund';

    protected $notifiable = true;


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

        return $this->response = new AopTradeRefundResponse($this, $data);
    }


    public function validateParams()
    {
        parent::validateParams();

        $this->validateBizContent('refund_amount');

        $this->validateBizContentOne(
            'out_trade_no',
            'trade_no'
        );
    }
}
