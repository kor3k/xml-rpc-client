<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Request;

use kor3k\XmlRpc\Parser\ParserOptions;

interface RequestParserInterface
{
    public function parseRequestBody(RequestInterface $request, ParserOptions $options = new ParserOptions()): string;
}
