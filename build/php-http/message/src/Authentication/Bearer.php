<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message\Authentication;

use Wenprise\Alipay\Http\Message\Authentication;
use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

/**
 * Authenticate a PSR-7 Request using a token.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class Bearer implements Authentication
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    public function authenticate(RequestInterface $request)
    {
        $header = sprintf('Bearer %s', $this->token);

        return $request->withHeader('Authorization', $header);
    }
}
