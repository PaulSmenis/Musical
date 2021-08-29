<?php


namespace App\Form\DataTransformer;

use App\DTO\PitchDTO;
use App\Entity\Pitch;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PitchToDTODataTransformer implements DataTransformerInterface
{
    /**
     * @param  Pitch $pitch
     */
    public function transform($pitch): PitchDTO
    {
        return new PitchDTO(
            $pitch->getName(),
            $pitch->getAccidental(),
            $pitch->getOctave()
        );
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  PitchDTO $pitchDTO
     * @throws TransformationFailedException
     */
    public function reverseTransform($pitchDTO): Pitch
    {
        return new Pitch(
            $pitchDTO->getName(),
            $pitchDTO->getAccidental(),
            $pitchDTO->getOctave()
        );
    }
}