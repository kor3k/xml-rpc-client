<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Response;

readonly class Response implements ResponseInterface
{
    public function __construct(
        private string $rawBody,
        private iterable $params,
        private ?int $faultCode = null,
        private ?string $faultString = null,
    ) {
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }

    public function getParams(): iterable
    {
        return $this->params;
    }

    public function isFault(): bool
    {
        return !empty($this->faultCode) && !empty($this->faultString);
    }

    public function getFaultCode(): ?int
    {
        return $this->faultCode;
    }

    public function getFaultString(): ?string
    {
        return $this->faultString;
    }

    public function getIterator(): \Traversable
    {
        $it = \is_array($this->params) ? new \ArrayIterator($this->params) : new \IteratorIterator($this->params);
        $it->rewind();

        return new \NoRewindIterator($it);
    }
}
