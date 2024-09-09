<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay;

use Wenprise\Alipay\Omnipay\Alipay\Requests\AopTradePagePayRequest;
use Wenprise\Alipay\Omnipay\Common\Message\AbstractRequest;
use Wenprise\Alipay\Omnipay\Common\Message\RequestInterface;

/**
 * Class AopPageGateway
 *
 * @package Wenprise\Alipay\Omnipay\Alipay
 * @link    https://docs.open.alipay.com/api_1/alipay.trade.page.pay
 * @method RequestInterface authorize(array $options = array())
 * @method RequestInterface completeAuthorize(array $options = array())
 * @method RequestInterface capture(array $options = array())
 * @method RequestInterface void(array $options = array())
 * @method RequestInterface createCard(array $options = array())
 * @method RequestInterface updateCard(array $options = array())
 * @method RequestInterface deleteCard(array $options = [])
 */
class AopPageGateway extends AbstractAopGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Page Gateway';
    }


    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->getParameter('return_url');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('return_url', $value);
    }


    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(AopTradePagePayRequest::class, $parameters);
    }
}
