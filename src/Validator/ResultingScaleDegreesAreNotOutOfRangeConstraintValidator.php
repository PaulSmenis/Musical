<?php


namespace App\Validator;


use App\Entity\Scale;
use OutOfBoundsException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ResultingScaleDegreesAreNotOutOfRangeConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ResultingScaleDegreesAreNotOutOfRangeConstraint) {
            throw new UnexpectedTypeException($constraint, ResultingScaleDegreesAreNotOutOfRangeConstraint::class);
        }

        try {
            new Scale(
                $constraint->data->getPitch(),
                $constraint->data->getFormula(),
                $constraint->data->getDegree()
            );
        } catch (OutOfBoundsException $e) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}