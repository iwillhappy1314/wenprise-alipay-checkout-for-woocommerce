<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 09-September-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Discovery;

use Wenprise\Alipay\Http\Client\HttpAsyncClient;
use Wenprise\Alipay\Http\Discovery\Exception\DiscoveryFailedException;

/**
 * Finds an HTTP Asynchronous Client.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class HttpAsyncClientDiscovery extends ClassDiscovery
{
    /**
     * Finds an HTTP Async Client.
     *
     * @return HttpAsyncClient
     *
     * @throws Exception\NotFoundException
     */
    public static function find()
    {
        try {
            $asyncClient = static::findOneByType(HttpAsyncClient::class);
        } catch (DiscoveryFailedException $e) {
            throw new NotFoundException('No HTTPlug async clients found. Make sure to install a package providing "php-http/async-client-implementation". Example: "php-http/guzzle6-adapter".', 0, $e);
        }

        return static::instantiateClass($asyncClient);
    }
}
