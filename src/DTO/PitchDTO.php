<?php


namespace App\DTO;


class PitchDTO
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $accidental;

    /**
     * @var int|null
     */
    private $octave;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getAccidental(): ?string
    {
        return $this->accidental;
    }

    /**
     * @param string|null $accidental
     */
    public function setAccidental(?string $accidental): void
    {
        $this->accidental = $accidental;
    }

    /**
     * @return int|null
     */
    public function getOctave(): ?int
    {
        return $this->octave;
    }

    /**
     * @param int|null $octave
     */
    public function setOctave(?int $octave): void
    {
        $this->octave = $octave;
    }
}