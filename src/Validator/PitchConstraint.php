<?php


namespace App\Validator;


use App\DTO\PitchDTO;
use App\Service\FormProcessingService;
use Symfony\Component\Validator\Constraint;

class PitchConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = FormProcessingService::JSON_VALIDATION_ERROR_MESSAGE;

    /**
     * @var PitchDTO
     */
    public $data;
}
