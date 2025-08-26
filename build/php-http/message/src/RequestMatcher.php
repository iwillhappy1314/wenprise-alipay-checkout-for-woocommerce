<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message;

use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

/**
 * Match a request.
 *
 * PSR-7 equivalent of Symfony's RequestMatcher
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/RequestMatcherInterface.php
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
interface RequestMatcher
{
    /**
     * Decides whether the rule(s) implemented by the strategy matches the supplied request.
     *
     * @param RequestInterface $request The PSR7 request to check for a match
     *
     * @return bool true if the request matches, false otherwise
     */
    public function matches(RequestInterface $request);
}
