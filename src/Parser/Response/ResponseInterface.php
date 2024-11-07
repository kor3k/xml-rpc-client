<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Response;

interface ResponseInterface extends \IteratorAggregate
{
    public function getParams(): iterable;
    public function isFault(): bool;
    public function getFaultCode(): ?int;
    public function getFaultString(): ?string;
}
