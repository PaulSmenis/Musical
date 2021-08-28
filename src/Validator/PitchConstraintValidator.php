<?php


namespace App\Validator;


use App\Entity\Pitch;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Throwable;

class PitchConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PitchConstraint) {
            throw new UnexpectedTypeException($constraint, PitchConstraint::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value) && !is_int($value)) {
            throw new UnexpectedValueException($value, 'string|int');
        }

        try {
            new Pitch($constraint->data->getName(), $constraint->data->getAccidental(), $constraint->data->getOctave());
        } catch (Throwable $e) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter($e->getMessage(), $value)
                ->addViolation();
        }
    }
}