<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

interface DenormalizerInterface
{
    /**
     * @param iterable<DataValueInterface> $data
     */
    public function denormalize(iterable $data): iterable;
}
