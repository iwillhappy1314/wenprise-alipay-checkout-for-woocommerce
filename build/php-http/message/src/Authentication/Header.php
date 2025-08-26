<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message\Authentication;

use Wenprise\Alipay\Http\Message\Authentication;
use Wenprise\Alipay\Psr\Http\Message\RequestInterface;

class Header implements Authentication
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|string[]
     */
    private $value;

    /**
     * @param string|string[] $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function authenticate(RequestInterface $request)
    {
        return $request->withHeader($this->name, $this->value);
    }
}
