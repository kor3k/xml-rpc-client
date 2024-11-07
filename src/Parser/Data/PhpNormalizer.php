<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

use kor3k\XmlRpc\Parser\ParserOptions;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * does >>process<< values (eg. base64_encode, datetime->format() etc).
 */
class PhpNormalizer implements NormalizerInterface
{
    public function __construct(public ParserOptions $options = new ParserOptions())
    {
    }

    /**
     * @return iterable<DataValueInterface>
     */
    public function normalize(iterable $data): iterable
    {
        return (function () use ($data) {
            foreach ($data as $key => $value) {
                yield $key => $this->normalizeValue($value);
            }
        })();
    }

    protected function normalizeIterable(iterable|object $value): DataValueInterface
    {
        $type = DataType::STRUCT;

        if (\is_iterable($value)) {
            $value = \iterator_to_array($value);

            if (self::onlyNumKeys($value)) {
                $type = DataType::ARRAY;
            }
        }

        $data = [];

        foreach ($value as $key => $member) {
            $data[$key] = $this->normalizeValue($member);
        }

        return new DataValue(value: $data, type: $type);
    }

    private static function onlyNumKeys(array $arr): bool
    {
        return \count(\array_filter(\array_keys($arr), \is_int(...))) === \count(\array_keys($arr));
    }

    public function normalizeValue(mixed $value): DataValueInterface
    {
        $data = match (true) {
            $value instanceof DataValueInterface => $value,
            $value instanceof StreamInterface => new DataValue(\stream_get_contents($value->detach()), DataType::BASE64),
            $value instanceof \SplFileInfo => new DataValue(\file_get_contents($value->getPathname()), DataType::BASE64),
            \class_exists(File::class) && $value instanceof File => new DataValue($value->getContent(), DataType::BASE64),
            $value instanceof \DateTimeInterface => new DataValue($value, DataType::DATETIME),
            \is_object($value),
            \is_iterable($value) => $this->normalizeIterable($value),
            \is_string($value) => new DataValue($value, DataType::STRING),
            \is_float($value) => new DataValue($value, DataType::DOUBLE),
            \is_integer($value) => new DataValue($value, DataType::INTEGER),
            \is_bool($value) => new DataValue($value, DataType::BOOLEAN),
            \is_resource($value) => new DataValue(\stream_get_contents($value), DataType::BASE64),
            default => new DataValue(null, DataType::NULL),
        };

        if (DataType::DATETIME === $data->getType()) {
            $dt = $data->getValue();

            if (!$dt instanceof \DateTimeInterface) {
                $dt = new \DateTimeImmutable((string) $dt);
            }

            return new DataValue($dt->format($this->options->dateFormat), DataType::DATETIME);
        }

        return match ($data->getType()) {
            DataType::BASE64 => new DataValue(\base64_encode((string) $data->getValue()), $data->getType()),
            DataType::DOUBLE => new DataValue((float) $data->getValue(), $data->getType()),
            DataType::INTEGER_4,
            DataType::INTEGER_8,
            DataType::INTEGER => new DataValue((int) $data->getValue(), $data->getType()),
            DataType::BOOLEAN => new DataValue((bool) $data->getValue(), $data->getType()),
            DataType::STRING => new DataValue((string) $data->getValue(), $data->getType()),
            DataType::NULL => new DataValue(null, $data->getType()),
            default => $data,
        };
    }
}
