<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay;

use Wenprise\Alipay\Omnipay\Alipay\Requests\LegacyAppPurchaseRequest;
use Wenprise\Alipay\Omnipay\Alipay\Requests\LegacyRefundNoPwdRequest;
use Wenprise\Alipay\Omnipay\Common\Message\AbstractRequest;
use Wenprise\Alipay\Omnipay\Common\Message\RequestInterface;

/**
 * Class LegacyAppGateway
 *
 * @package Wenprise\Alipay\Omnipay\Alipay
 * @link    https://docs.open.alipay.com/59/103563
 * @method RequestInterface authorize(array $options = [])
 * @method RequestInterface completeAuthorize(array $options = [])
 * @method RequestInterface capture(array $options = [])
 * @method RequestInterface void(array $options = [])
 * @method RequestInterface createCard(array $options = [])
 * @method RequestInterface updateCard(array $options = [])
 * @method RequestInterface deleteCard(array $options = [])
 */
class LegacyAppGateway extends AbstractLegacyGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Legacy APP Gateway';
    }


    public function getDefaultParameters()
    {
        $data = parent::getDefaultParameters();

        $data['signType'] = 'RSA';

        return $data;
    }


    /**
     * @return mixed
     */
    public function getRnCheck()
    {
        return $this->getParameter('rn_check');
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function setRnCheck($value)
    {
        return $this->setParameter('rn_check', $value);
    }


    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(LegacyAppPurchaseRequest::class, $parameters);
    }


    /**
     * @param array $parameters
     *
     * @return LegacyRefundNoPwdRequest|AbstractRequest
     */
    public function refundNoPwd(array $parameters = [])
    {
        return $this->createRequest(LegacyRefundNoPwdRequest::class, $parameters);
    }
}
