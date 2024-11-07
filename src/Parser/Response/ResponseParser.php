<?php declare(strict_types=1);

namespace kor3k\XmlRpc\Parser\Response;

use kor3k\XmlRpc\Parser\Data\PhpDenormalizer;
use kor3k\XmlRpc\Parser\Data\RpcNormalizer;
use kor3k\XmlRpc\Parser\ParserOptions;
use Symfony\Component\Serializer\Context\Encoder\XmlEncoderContextBuilder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

use function Symfony\Component\String\u;

readonly class ResponseParser implements ResponseParserInterface
{
    public function __construct(
        protected DecoderInterface $serializer,
        protected RpcNormalizer $normalizer = new RpcNormalizer(),
        protected PhpDenormalizer $denormalizer = new PhpDenormalizer(),
    ) {
    }

    public function parseResponseBody(\Stringable|string $body, ParserOptions $options = new ParserOptions()): ResponseInterface
    {
        $this->denormalizer->options = $options;

        $body = (string) u((string) $body)->trim();

        if (empty($body)) {
            return new Response(rawBody: '', params: []);
        }

        $ctx = new XmlEncoderContextBuilder();
        $ctx = $ctx->withTypeCastAttributes(false)
                ->withRootNodeName('methodResponse')
                ->withEncoding($options->encoding)
                ->withAsCollection(false);

        $data = $this->serializer->decode(data: $body, format: 'xml', context: $ctx->toArray());

        if (!\is_array($data)) {
            return new Response(rawBody: $body, params: []);
        }

        $fault = \iterator_to_array($this->denormalizer->denormalize($this->normalizer->normalize($data['fault'] ?? [])));
        $params = \iterator_to_array($this->denormalizer->denormalize($this->normalizer->normalize($data['params']['param'] ?? [])));

        return new Response(
            rawBody: $body,
            params: $params,
            faultCode: $fault[0]['faultCode'] ?? null,
            faultString: $fault[0]['faultString'] ?? null,
        );
    }
}
