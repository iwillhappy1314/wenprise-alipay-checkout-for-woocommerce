<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\AopTradeCreateResponse;

/**
 * Class AopTradeCreateRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 * @link    https://docs.open.alipay.com/api_1/alipay.trade.create
 */
class AopTradeCreateRequest extends AbstractAopRequest
{
    protected $method = 'alipay.trade.create';


    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notify_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setNotifyUrl($value)
    {
        return $this->setParameter('notify_url', $value);
    }


    /**
     * @return mixed
     */
    public function getAppAuthToken()
    {
        return $this->getParameter('app_auth_token');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setAppAuthToken($value)
    {
        return $this->setParameter('app_auth_token', $value);
    }


    /**
     * @param mixed $data
     *
     * @return mixed|AopTradeCreateResponse|\Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface|\Wenprise\Alipay\Psr\Http\Message\StreamInterface
     * @throws \Wenprise\Alipay\Psr\Http\Client\Exception\NetworkException
     * @throws \Wenprise\Alipay\Psr\Http\Client\Exception\RequestException
     */
    public function sendData($data)
    {
        $data = parent::sendData($data);

        return $this->response = new AopTradeCreateResponse($this, $data);
    }
}
