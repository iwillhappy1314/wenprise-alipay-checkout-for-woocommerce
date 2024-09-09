<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

abstract class AbstractAopResponse extends AbstractResponse
{
    protected $key;


    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->getCode() == '10000';
    }


    public function getCode()
    {
        return $this->getAlipayResponse('code');
    }


    public function getAlipayResponse($key = null)
    {
        if ($key) {
            return array_get($this->data, "{$this->key}.{$key}");
        } else {
            return array_get($this->data, $this->key);
        }
    }


    public function getMessage()
    {
        return $this->getAlipayResponse('msg');
    }


    public function getSubCode()
    {
        return $this->getAlipayResponse('sub_code');
    }


    public function getSubMessage()
    {
        return $this->getAlipayResponse('sub_msg');
    }
}
