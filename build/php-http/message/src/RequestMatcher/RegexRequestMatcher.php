<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message\RequestMatcher;

use Wenprise\Alipay\Http\Message\RequestMatcher;
use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

@trigger_error('The '.__NAMESPACE__.'\RegexRequestMatcher class is deprecated since version 1.2 and will be removed in 2.0. Use Http\Message\RequestMatcher\RequestMatcher instead.', E_USER_DEPRECATED);

/**
 * Match a request with a regex on the uri.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since version 1.2 and will be removed in 2.0. Use {@link RequestMatcher} instead.
 */
final class RegexRequestMatcher implements RequestMatcher
{
    /**
     * Matching regex.
     *
     * @var string
     */
    private $regex;

    /**
     * @param string $regex
     */
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    public function matches(RequestInterface $request)
    {
        return (bool) preg_match($this->regex, (string) $request->getUri());
    }
}
