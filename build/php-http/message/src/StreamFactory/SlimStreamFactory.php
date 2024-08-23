<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Message\StreamFactory;

use Wenprise\Alipay\Http\Message\StreamFactory;
use Wenprise\Alipay\Psr\Http\Message\StreamInterface;
use Slim\Http\Stream;

if (!interface_exists(StreamFactory::class)) {
    throw new \LogicException('You cannot use "Wenprise\Alipay\Http\Message\MessageFactory\SlimStreamFactory" as the "php-http/message-factory" package is not installed. Try running "composer require php-http/message-factory". Note that this package is deprecated, use "psr/http-factory" instead');
}

/**
 * Creates Slim 3 streams.
 *
 * @author Mika Tuupola <tuupola@appelsiini.net>
 *
 * @deprecated This will be removed in php-http/message2.0. Consider using the official Slim PSR-17 factory
 */
final class SlimStreamFactory implements StreamFactory
{
    public function createStream($body = null)
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_resource($body)) {
            return new Stream($body);
        }

        $resource = fopen('php://memory', 'r+');
        $stream = new Stream($resource);
        if (null !== $body && '' !== $body) {
            $stream->write((string) $body);
        }

        return $stream;
    }
}
