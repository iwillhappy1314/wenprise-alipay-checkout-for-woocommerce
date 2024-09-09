<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message;

use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

/**
 * Add authentication information to a PSR-7 Request.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Authentication
{
    /**
     * Alter the request to add the authentication credentials.
     *
     * To do that, the implementation might use pre-stored credentials or do
     * separate HTTP requests to obtain a valid token.
     *
     * @param RequestInterface $request The request without authentication information
     *
     * @return RequestInterface The request with added authentication information
     */
    public function authenticate(RequestInterface $request);
}
