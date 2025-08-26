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
 * Authenticate a PSR-7 Request using Basic Auth.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class BasicAuth implements Authentication
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate(RequestInterface $request)
    {
        $header = sprintf('Basic %s', base64_encode(sprintf('%s:%s', $this->username, $this->password)));

        return $request->withHeader('Authorization', $header);
    }
}
