<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * Can be attached to any field in the form (since it verifies the whole form).
 *
 * Class ResultingScaleDegreesAreNotOutOfRangeConstraint
 * @package App\Validator
 */
class ResultingScaleDegreesAreNotOutOfRangeConstraint extends Constraint
{
    /**
     * @var string
     */
    public string $message = 'Due to parameters you have chosen, the scale contains a tone with more than 3 accidental signs in a row.';

    public $data;
}