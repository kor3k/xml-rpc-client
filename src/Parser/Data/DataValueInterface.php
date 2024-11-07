<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

interface DataValueInterface
{
    public function getValue(): mixed;
    public function getType(): DataType;
}
