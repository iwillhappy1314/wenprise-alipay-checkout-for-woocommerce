<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 23-August-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Wenprise\Alipay\Http\Discovery;

use Wenprise\Alipay\Psr\Http\Client\ClientInterface;
use Wenprise\Alipay\Psr\Http\Message\RequestFactoryInterface;
use Wenprise\Alipay\Psr\Http\Message\RequestInterface;
use Wenprise\Alipay\Psr\Http\Message\ResponseFactoryInterface;
use Wenprise\Alipay\Psr\Http\Message\ResponseInterface;
use Wenprise\Alipay\Psr\Http\Message\ServerRequestFactoryInterface;
use Wenprise\Alipay\Psr\Http\Message\StreamFactoryInterface;
use Wenprise\Alipay\Psr\Http\Message\UploadedFileFactoryInterface;
use Wenprise\Alipay\Psr\Http\Message\UriFactoryInterface;

/**
 * A generic PSR-18 and PSR-17 implementation.
 *
 * You can create this class with concrete client and factory instances
 * or let it use discovery to find suitable implementations as needed.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Psr18Client extends Psr17Factory implements ClientInterface
{
    private $client;

    public function __construct(
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        ?UriFactoryInterface $uriFactory = null
    ) {
        parent::__construct($requestFactory, $responseFactory, $serverRequestFactory, $streamFactory, $uploadedFileFactory, $uriFactory);

        $this->client = $client ?? Psr18ClientDiscovery::find();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
