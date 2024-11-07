<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Request;

use kor3k\XmlRpc\Parser\Data\PhpNormalizer;
use kor3k\XmlRpc\Parser\Data\RpcDenormalizer;
use kor3k\XmlRpc\Parser\ParserOptions;
use Symfony\Component\Serializer\Context\Encoder\XmlEncoderContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

readonly class RequestParser implements RequestParserInterface
{
    public function __construct(
        protected SerializerInterface $serializer,
        protected PhpNormalizer $normalizer = new PhpNormalizer(),
        protected RpcDenormalizer $denormalizer = new RpcDenormalizer(),
    ) {
    }

    public function parseRequestBody(RequestInterface $request, ParserOptions $options = new ParserOptions()): string
    {
        $this->normalizer->options = $options;

        $data = \iterator_to_array($this->denormalizer->denormalize($this->normalizer->normalize($request)));
        $data = \array_values($data);
        $xml = [
            'methodName' => $request->getMethod(),
            'params' => [
                'param' => $data,
            ],
        ];

        $ctx = new XmlEncoderContextBuilder();
        $ctx = $ctx
                ->withTypeCastAttributes(false)
                ->withRootNodeName('methodCall')
                ->withFormatOutput($options->prettyPrint)
                ->withEncoding($options->encoding);

        return $this->serializer->serialize(data: $xml, format: 'xml', context: $ctx->toArray());
    }
}
