<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay;

use Wenprise\Alipay\Omnipay\Alipay\Requests\LegacyExpressPurchaseRequest;
use Wenprise\Alipay\Omnipay\Common\Message\AbstractRequest;
use Wenprise\Alipay\Omnipay\Common\Message\RequestInterface;

/**
 * Class LegacyExpressGateway
 *
 * @package Wenprise\Alipay\Omnipay\Alipay
 * @link    https://alipay.open.taobao.com/docs/doc.htm?treeId=108&articleId=103950&docType=1
 * @method RequestInterface authorize(array $options = [])
 * @method RequestInterface completeAuthorize(array $options = [])
 * @method RequestInterface capture(array $options = [])
 * @method RequestInterface void(array $options = [])
 * @method RequestInterface createCard(array $options = [])
 * @method RequestInterface updateCard(array $options = [])
 * @method RequestInterface deleteCard(array $options = [])
 */
class LegacyExpressGateway extends AbstractLegacyGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Legacy Express Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(LegacyExpressPurchaseRequest::class, $parameters);
    }
}
