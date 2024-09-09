<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Client;

use Wenprise\Alipay\Http\Promise\Promise;
use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

/**
 * Sends a PSR-7 Request in an asynchronous way by returning a Promise.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
interface HttpAsyncClient
{
    /**
     * Sends a PSR-7 request in an asynchronous way.
     *
     * Exceptions related to processing the request are available from the returned Promise.
     *
     * @return Promise resolves a PSR-7 Response or fails with an Http\Client\Exception
     *
     * @throws \Exception If processing the request is impossible (eg. bad configuration).
     */
    public function sendAsyncRequest(RequestInterface $request);
}
