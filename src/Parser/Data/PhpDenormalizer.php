<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

use kor3k\XmlRpc\Parser\ParserOptions;

class PhpDenormalizer implements DenormalizerInterface
{
    public function __construct(public ParserOptions $options = new ParserOptions())
    {
    }

    /**
     * @param iterable<DataValueInterface> $data
     */
    public function denormalize(iterable $data): iterable
    {
        return (function () use ($data) {
            foreach ($data as $key => $value) {
                yield $key => $this->denormalizeValue($value);
            }
        })();
    }

    public function denormalizeValue(DataValueInterface $data): mixed
    {
        return match ($data->getType()) {
            DataType::STRUCT => $this->denormalizeStruct($data),
            DataType::ARRAY => $this->denormalizeArray($data),
            DataType::DATETIME => $this->denormalizeDatetime($data),
            DataType::BASE64 => $this->denormalizeBase64($data),
            DataType::NULL => $this->denormalizeNull($data),
            default => $this->denormalizeScalar($data),
        };
    }

    protected function denormalizeStruct(DataValueInterface $data): iterable
    {
        if (DataType::STRUCT !== $data->getType()) {
            throw new \RuntimeException('not a struct');
        }

        $ret = [];

        foreach ($data->getValue() as $key => $value) {
            $ret[$key] = $this->denormalizeValue($value);
        }

        return $ret;
    }

    protected function denormalizeArray(DataValueInterface $data): iterable
    {
        if (DataType::ARRAY !== $data->getType()) {
            throw new \RuntimeException('not an array');
        }

        $ret = [];

        foreach ($data->getValue() as $key => $value) {
            $ret[] = $this->denormalizeValue($value);
        }

        return $ret;
    }

    protected function denormalizeDatetime(DataValueInterface $data): \DateTimeInterface
    {
        if (DataType::DATETIME !== $data->getType()) {
            throw new \RuntimeException('not a datetime');
        }

        if ($data->getValue() instanceof \DateTimeInterface) {
            return $data->getValue();
        }

        try {
            $value = \DateTimeImmutable::createFromFormat($this->options->dateFormat, (string) $data->getValue());
            if (!$value) {
                throw new \ValueError('invalid datetime');
            }
        } catch (\Throwable $exception) {
            $value = new \DateTimeImmutable((string) $data->getValue());
        }

        return $value;
    }

    protected function denormalizeBase64(DataValueInterface $data): string
    {
        if (DataType::BASE64 !== $data->getType()) {
            throw new \RuntimeException('not a base64 value');
        }

        $value = \base64_decode((string) $data->getValue());

        return $value;
    }

    protected function denormalizeScalar(DataValueInterface $data): string|int|bool|float
    {
        $value = match ($data->getType()) {
            default => throw new \RuntimeException(\sprintf('not a scalar type: %s', $data->getType()->name)),
            DataType::STRING => (string) $data->getValue(),
            DataType::INTEGER_4,
            DataType::INTEGER_8,
            DataType::INTEGER => (int) $data->getValue(),
            DataType::DOUBLE => (float) $data->getValue(),
            DataType::BOOLEAN => (bool) $data->getValue(),
        };

        return $value;
    }

    protected function denormalizeNull(DataValueInterface $data): null
    {
        if (DataType::NULL !== $data->getType()) {
            throw new \RuntimeException('not a null');
        }

        return null;
    }
}
