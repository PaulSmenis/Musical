<?php


namespace App\DTO;

use App\Entity\Pitch;
use JetBrains\PhpStorm\Pure;

class ScaleDTO
{
    /**
     * @var PitchDTO|null
     */
    private $pitch;

    /**
     * @var string|null
     */
    private $formula;

    /**
     * @var string|null
     */
    private $degree;

    public function __construct(
        ?Pitch $pitch = null,
        ?string $formula = null,
        ?string $degree = null
    )
    {
        $this->pitch = new Pitch;
        $this->formula = null;
        $this->degree = null;
    }

    /**
     * @return Pitch|null
     */
    public function getPitch(): ?Pitch
    {
        return $this->pitch;
    }

    /**
     * @param Pitch|null $pitch
     */
    public function setPitch(?Pitch $pitch): void
    {
        $this->pitch = $pitch;
    }

    /**
     * @return string|null
     */
    public function getFormula(): ?string
    {
        return $this->formula;
    }

    /**
     * @param string|null $formula
     */
    public function setFormula(?string $formula): void
    {
        $this->formula = $formula;
    }

    /**
     * @return string|null
     */
    public function getDegree(): ?string
    {
        return $this->degree;
    }

    /**
     * @param string|null $degree
     */
    public function setDegree(?string $degree): void
    {
        $this->degree = $degree;
    }
}