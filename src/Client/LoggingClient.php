<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class LoggingClient extends Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected function onRequest(RequestInterface $request): RequestInterface
    {
        $request = parent::onRequest($request);

        if (!$this->logger) {
            return $request;
        }

        $body = (string) $request->getBody();

        $this->logger->debug('Sending xml-rpc request', [
            'uri' => (string) $request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => $body,
        ]);

        return $request->withBody($this->streamFactory->createStream($body));
    }
}
