<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

/**
 * does NOT >>process<< values (eg. base64_encode, datetime->format() etc).
 */
class RpcDenormalizer implements DenormalizerInterface
{
    /**
     * @param iterable<DataValueInterface> $data
     */
    public function denormalize(iterable $data): iterable
    {
        return (function () use ($data) {
            foreach ($data as $name => $value) {
                yield $name => ['value' => \iterator_to_array($this->denormalizeValue($value))];
            }
        })();
    }

    public function denormalizeValue(DataValueInterface $data): iterable
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

    protected function denormalizeArray(DataValueInterface $data): iterable
    {
        if (DataType::ARRAY !== $data->getType()) {
            throw new \RuntimeException('not an array');
        }

        if (empty($data->getValue())) {
            return ['array' => ['data' => []]];
        }

        $ret = ['array' => ['data' => ['value' => []]]];

        foreach ($data->getValue() as $member) {
            $ret['array']['data']['value'][] = \iterator_to_array($this->denormalizeValue($member));
        }

        return $ret;
    }

    protected function denormalizeStruct(DataValueInterface $data): iterable
    {
        if (DataType::STRUCT !== $data->getType()) {
            throw new \RuntimeException('not a struct');
        }

        $ret = ['struct' => ['member' => []]];

        foreach ($data->getValue() as $name => $member) {
            $ret['struct']['member'][] = [
                'name' => $name,
                'value' => \iterator_to_array($this->denormalizeValue($member)),
            ];
        }

        return $ret;
    }

    protected function denormalizeDatetime(DataValueInterface $data): iterable
    {
        if (DataType::DATETIME !== $data->getType()) {
            throw new \RuntimeException('not a datetime');
        }

        return [$data->getType()->value => (string) $data->getValue()];
    }

    protected function denormalizeBase64(DataValueInterface $data): iterable
    {
        if (DataType::BASE64 !== $data->getType()) {
            throw new \RuntimeException('not a base64 value');
        }

        $value = $data->getValue();

        return [$data->getType()->value => $value];
    }

    protected function denormalizeScalar(DataValueInterface $data): iterable
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

        return [$data->getType()->value => $value];
    }

    protected function denormalizeNull(DataValueInterface $data): iterable
    {
        if (DataType::NULL !== $data->getType()) {
            throw new \RuntimeException('not a null');
        }

        return [$data->getType()->value => ''];
    }
}
