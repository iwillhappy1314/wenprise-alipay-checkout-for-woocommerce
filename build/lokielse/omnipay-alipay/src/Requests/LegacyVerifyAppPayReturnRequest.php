<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Requests;

use Wenprise\Alipay\Omnipay\Alipay\Responses\LegacyNotifyResponse;
use Wenprise\Alipay\Omnipay\Common\Exception\InvalidRequestException;
use Wenprise\Alipay\Omnipay\Common\Message\ResponseInterface;

/**
 * Class LegacyVerifyAppPayReturnRequest
 * @package Wenprise\Alipay\Omnipay\Alipay\Requests
 */
class LegacyVerifyAppPayReturnRequest extends AbstractLegacyRequest
{

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validateParams();

        $result = trim($this->getResult());

        if (substr($result, -2, 2) == '\"') {
            $result = stripslashes($result);
        }

        parse_str($result, $data);

        $sign     = trim($data['sign'], '"');
        $sign     = str_replace(' ', '+', $sign);
        $signType = trim($data['sign_type'], '"');

        $data['sign']      = $sign;
        $data['sign_type'] = $signType;

        return $data;
    }


    /**
     * @throws InvalidRequestException
     */
    public function validateParams()
    {
        $this->validate(
            'result'
        );

        $result = $this->getResult();

        if (! is_string($result)) {
            throw new InvalidRequestException('The result should be string');
        }

        parse_str($result, $data);

        if (! isset($data['sign'])) {
            throw new InvalidRequestException('The `result` is invalid');
        }

        if (! isset($data['sign_type'])) {
            throw new InvalidRequestException('The `result` is invalid');
        }
    }


    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->getParameter('result');
    }


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
        $request = new LegacyNotifyRequest($this->httpClient, $this->httpRequest);
        $request->initialize($this->parameters->all());
        $request->setParams($data);
        $request->setSort(false);
        $request->setAlipayPublicKey($this->getAlipayPublicKey());

        /**
         * @var LegacyNotifyResponse $response
         */
        $response = $request->send();

        return $response;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setResult($value)
    {
        return $this->setParameter('result', $value);
    }
}
