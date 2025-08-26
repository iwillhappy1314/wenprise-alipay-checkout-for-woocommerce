<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Omnipay\Common\Http;

use Wenprise\Alipay\Psr\Http\Message\RequestInterface;
use Throwable;

abstract class Exception extends \RuntimeException
{
    /** @var RequestInterface  */
    protected $request;

    public function __construct($message, RequestInterface $request, $previous = null)
    {
        $this->request = $request;

        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
