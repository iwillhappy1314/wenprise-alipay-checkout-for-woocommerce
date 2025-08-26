<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradeCreateRequest;
use Wenprise\Alipay\Omnipay\Common\Message\AbstractRequest;
use Wenprise\Alipay\Omnipay\Common\Message\RequestInterface;

/**
 * Class AopJsGateway
 *
 * @package Wenprise\Alipay\Omnipay\Alipay
 * @link    https://docs.open.alipay.com/api_1/alipay.trade.create
 * @link    https://myjsapi.alipay.com/jsapi/native/trade-pay.html
 * @method RequestInterface authorize(array $options = array())
 * @method RequestInterface completeAuthorize(array $options = array())
 * @method RequestInterface capture(array $options = array())
 * @method RequestInterface void(array $options = array())
 * @method RequestInterface createCard(array $options = array())
 * @method RequestInterface updateCard(array $options = array())
 * @method RequestInterface deleteCard(array $options = [])
 */
class AopJsGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Js Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(AopTradeCreateRequest::class, $parameters);
    }
}
