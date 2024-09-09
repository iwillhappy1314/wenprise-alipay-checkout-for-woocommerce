<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message;

/**
 * Factory for PSR-7 Request and Response.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @deprecated since version 1.1, use Wenprise\Alipay\Psr\Http\Message\RequestFactoryInterface and Psr\Http\Message\ResponseFactoryInterface instead.
 */
interface MessageFactory extends RequestFactory, ResponseFactory
{
}
