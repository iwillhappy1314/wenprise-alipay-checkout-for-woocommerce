<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Alipay;

use Wenprise\Alipay\Omnipay\Alipay\Requests\LegacyWapPurchaseRequest;
use Wenprise\Alipay\Omnipay\Common\Message\AbstractRequest;
use Wenprise\Alipay\Omnipay\Common\Message\RequestInterface;

/**
 * Class LegacyWapGateway
 *
 * @package  Wenprise\Alipay\Omnipay\Alipay
 * @link     https://docs.open.alipay.com/60/103564
 * @method RequestInterface authorize(array $options = [])
 * @method RequestInterface completeAuthorize(array $options = [])
 * @method RequestInterface capture(array $options = [])
 * @method RequestInterface void(array $options = [])
 * @method RequestInterface createCard(array $options = [])
 * @method RequestInterface updateCard(array $options = [])
 * @method RequestInterface deleteCard(array $options = [])
 */
class LegacyWapGateway extends AbstractLegacyGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'Alipay Legacy Wap Gateway';
    }


    /**
     * @param array $parameters
     *
     * @return LegacyWapPurchaseRequest|AbstractRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(LegacyWapPurchaseRequest::class, $parameters);
    }
}
