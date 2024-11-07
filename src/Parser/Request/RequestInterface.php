<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Request;

interface RequestInterface extends \IteratorAggregate
{
    public function getMethod(): string;
    public function getParams(): iterable;
}
