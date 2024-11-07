<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

readonly class DataValue implements DataValueInterface
{
    public function __construct(private mixed $value, private DataType $type)
    {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getType(): DataType
    {
        return $this->type;
    }
}
