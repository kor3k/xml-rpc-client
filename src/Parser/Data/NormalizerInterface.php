<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Data;

interface NormalizerInterface
{
    /**
     * @return iterable<DataValueInterface>
     */
    public function normalize(iterable $data): iterable;
}
