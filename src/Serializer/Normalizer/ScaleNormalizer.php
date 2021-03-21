<?php


namespace App\Serializer\Normalizer;


use App\Structures\Scale;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;


class ScaleNormalizer implements NormalizerInterface
{
    /**
     * @param Scale $object
     * @param null $format
     * @param array $context
     * @return array
     */
    public function normalize(mixed $object, $format = null, array $context = []): array
    {
        $return = [];
        $i = 0;
        foreach ($object->getPitches() as $pitch) {
            $return[$i] = (new PitchNormalizer)->normalize($pitch, $format, $context);
            $i++;
        }
        return $return;
    }

    /**
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization(mixed $data, $format = null): bool
    {
        return $data instanceof Scale;
    }
}