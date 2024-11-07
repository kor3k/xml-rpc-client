<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Response;

use kor3k\XmlRpc\Parser\ParserOptions;

interface ResponseParserInterface
{
    public function parseResponseBody(string|\Stringable $body, ParserOptions $options = new ParserOptions()): ResponseInterface;
}
