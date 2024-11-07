<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

class RpcNormalizer implements NormalizerInterface
{
    /**
     * @return iterable<DataValueInterface>
     */
    public function normalize(iterable $data): iterable
    {
        $data = \iterator_to_array($data);

        if (empty($data[0])) {
            $data = [$data];
        }

        return (function () use ($data) {
            foreach ($data as $param) {
                $param = $param['value'] ?? [];

                foreach ($param as $type => $value) {
                    yield $this->normalizeValue($type, $value);
                }
            }
        })();
    }

    protected function normalizeArray(DataValueInterface $value): DataValueInterface
    {
        $value = $value->getValue();
        $value = $value['data']['value'] ?? [];

        if (empty($value[0])) {
            $value = [$value];
        }

        $ret = [];

        foreach ($value as $member) {
            foreach ($member as $type => $data) {
                $ret[] = $this->normalizeValue($type, $data);
            }
        }

        return new DataValue(value: $ret, type: DataType::ARRAY);
    }

    protected function normalizeStruct(DataValueInterface $value): DataValueInterface
    {
        $value = $value->getValue();
        $value = $value['member'] ?? [];

        if (empty($value[0])) {
            $value = [$value];
        }

        $ret = [];

        foreach ($value as $member) {
            if (!isset($member['name']) || !isset($member['value'])) {
                continue;
            }

            $key = $member['name'];
            foreach ($member['value'] as $type => $data) {
                $ret[$key] = $this->normalizeValue($type, $data);
            }
        }

        return new DataValue(value: $ret, type: DataType::STRUCT);
    }

    public function normalizeValue(string $type, mixed $value): DataValueInterface
    {
        try {
            $data = new DataValue(value: $value, type: DataType::from($type));
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException(sprintf('Invalid type: %s', $type));
        }

        return match ($data->getType()) {
            DataType::ARRAY => $this->normalizeArray($data),
            DataType::STRUCT => $this->normalizeStruct($data),
            DataType::DATETIME,
            DataType::BASE64,
            DataType::STRING => new DataValue((string) $data->getValue(), $data->getType()),
            DataType::DOUBLE => new DataValue((float) $data->getValue(), $data->getType()),
            DataType::INTEGER_4,
            DataType::INTEGER_8,
            DataType::INTEGER => new DataValue((int) $data->getValue(), $data->getType()),
            DataType::BOOLEAN => new DataValue((bool) $data->getValue(), $data->getType()),
            DataType::NULL => new DataValue(null, $data->getType()),
            default => new DataValue(null, DataType::NULL),
        };
    }
}
