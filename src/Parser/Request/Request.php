<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Request;

readonly class Request implements RequestInterface
{
    public function __construct(
        private string $method,
        private iterable $params,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): iterable
    {
        return $this->params;
    }

    public function withParams(iterable $params): self
    {
        return new self(
            method: $this->method,
            params: $params,
        );
    }

    public function getIterator(): \Traversable
    {
        $it = \is_array($this->params) ? new \ArrayIterator($this->params) : new \IteratorIterator($this->params);
        $it->rewind();

        return new \NoRewindIterator($it);
    }
}
