<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Client;

use kor3k\XmlRpc\Parser\ParserOptions;
use kor3k\XmlRpc\Parser\Request\Request as RpcRequest;
use kor3k\XmlRpc\Parser\Request\RequestInterface as RpcRequestInterface;
use kor3k\XmlRpc\Parser\Request\RequestParserInterface as RpcRequestParser;
use kor3k\XmlRpc\Parser\Response\ResponseInterface as RpcResponseInterface;
use kor3k\XmlRpc\Parser\Response\ResponseParserInterface as RpcResponseParser;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * override ::onRequest() or use $onRequest for setting hostname, uri, auth, etc.
 */
class Client
{
    private \Closure $onRequest;

    public function __construct(
        protected readonly ClientInterface $httpClient,
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly StreamFactoryInterface $streamFactory,
        protected readonly RpcRequestParser $rpcRequestParser,
        protected readonly RpcResponseParser $rpcResponseParser,
        ?\Closure $onRequest = null,
        public ParserOptions $parserOptions = new ParserOptions(),
    ) {
        $this->onRequest = $onRequest ?? static fn (RequestInterface $request) => $request;
    }

    public function sendRpcRequest(RpcRequestInterface $rpcRequest): RpcResponseInterface
    {
        $request = $this->requestFactory->createRequest('POST', '/RPC2');
        $request = $request
            ->withHeader('Content-Type', 'application/xml')
            ->withBody($this->streamFactory->createStream($this->rpcRequestParser->parseRequestBody(
                request: $rpcRequest,
                options: $this->parserOptions,
            )));

        $response = $this->httpClient->sendRequest($this->onRequest($request));

        return $this->createRpcResponse($response->getBody());
    }

    public function createRpcRequest(string $method, iterable $params = []): RpcRequestInterface
    {
        return new RpcRequest(method: $method, params: $params);
    }

    public function createRpcResponse(string|\Stringable $body): RpcResponseInterface
    {
        return $this->rpcResponseParser->parseResponseBody(
            body: (string) $body,
            options: $this->parserOptions,
        );
    }

    protected function onRequest(RequestInterface $request): RequestInterface
    {
        return ($this->onRequest)($request);
    }
}
