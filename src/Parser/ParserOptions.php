<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser;

readonly class ParserOptions
{
    public const string DATE_FORMAT = 'Y-m-d\TH:i:s';
    public const string ENCODING = 'iso-8859-1';

    public function __construct(
        public bool $prettyPrint = false,
        public string $encoding = self::ENCODING,
        public string $dateFormat = self::DATE_FORMAT,
    ) {
    }
}
