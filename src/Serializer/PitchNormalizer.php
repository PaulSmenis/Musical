<?php


namespace App\Serializer\Normalizer;


use App\Structures\Pitch;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;

class PitchNormalizer implements NormalizerInterface
{
    /**
     * @param Pitch $object
     * @param null $format
     * @param array $context
     * @return array
     */
    #[ArrayShape(['name' => "string", 'accidental' => "string", 'octave' => "int"])]
    public function normalize(mixed $object, $format = null, array $context = []): array
    {
        return [
            'name' => $object->getName(),
            'accidental' => $object->getAccidental(),
            'octave' => $object->getOctaveSpn()
        ];
    }


    /**
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization(mixed $data, $format = null): bool
    {
        return $data instanceof Pitch;
    }

    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}