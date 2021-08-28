<?php


namespace App\Serializer\Normalizer;


use Throwable;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExceptionNormalizer implements NormalizerInterface
{
    /**
     * @param Throwable $object
     * @param null $format
     * @param array $context
     * @return array
     */
    #[ArrayShape(['message' => "string", 'file' => "string", 'line' => "string"])]
    public function normalize(mixed $object, $format = null, array $context = []): array
    {
        return [
            'message' => $object->getMessage(),
            'file' => $object->getFile(),
            'line' => $object->getLine()
        ];
    }

    /**
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization(mixed $data, $format = null): bool
    {
        return $data instanceof Throwable;
    }
}