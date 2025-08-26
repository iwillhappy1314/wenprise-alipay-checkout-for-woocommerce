<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay\Responses;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeWapPayRequest;
use Wenprise\Alipay\Omnipay\Common\Message\RedirectResponseInterface;

class AopTradePagePayResponse extends AbstractResponse implements RedirectResponseInterface
{

    /**
     * @var AopTradeWapPayRequest
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


    public function isRedirect()
    {
        return true;
    }


    /**
     * Gets the redirect target url.
     */
    public function getRedirectUrl()
    {
        return sprintf('%s?%s', $this->request->getEndpoint(), http_build_query($this->data));
    }


    /**
     * Get the required redirect method (either GET or POST).
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }


    /**
     * Gets the redirect form data array, if the redirect method is POST.
     */
    public function getRedirectData()
    {
        return $this->getData();
    }
}
