<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Client\Exception;

use Wenprise\Alipay\Psr\Http\Client\NetworkExceptionInterface as PsrNetworkException;
use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

/**
 * Thrown when the request cannot be completed because of network issues.
 *
 * There is no response object as this exception is thrown when no response has been received.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class NetworkException extends TransferException implements PsrNetworkException
{
    use RequestAwareTrait;

    /**
     * @param string $message
     */
    public function __construct($message, RequestInterface $request, \Exception $previous = null)
    {
        $this->setRequest($request);

        parent::__construct($message, 0, $previous);
    }
}
