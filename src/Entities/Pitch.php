<?php


namespace App\Structures;
use Symfony\Component\Config\Definition\Exception\Exception;

class Pitch
{
    public const NAMES = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
    public const ACCIDENTALS = ['#', 'b', 'natural'];

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $accidental
     */
    private string $accidental;

    /**
     * @var int $octave_spn
     */
    private int $octave_spn;

    /**
     * Pitch constructor.
     * @param string|null $name
     * @param string|null $accidental
     * @param int|null $octave_spn
     * @throws \Exception
     */
    public function __construct(
        ?string $name = null,
        ?string $accidental = null,
        ?int $octave_spn = null
    )
    {
        if (!$name) {
            $this->name = $this::NAMES[array_rand($this::NAMES)];
        } else {
            $this->name = $name;
        }

        if (!$accidental) {
            $this->accidental = $this::ACCIDENTALS[array_rand($this::ACCIDENTALS)];
        } else {
            $this->accidental = $accidental;
        }

        if (!$octave_spn) {
            $this->octave_spn = random_int(0, 8);
        } else {
            $this->octave_spn = $octave_spn;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        if (in_array($name, $name::NAMES)) {
            $this->name = $name;
        } else {
            throw new Exception('Name is not an appropriate value');
        }
    }

    /**
     * @return string
     */
    public function getAccidental(): string
    {
        return $this->accidental;
    }

    /**
     * @param string $accidental
     */
    public function setAccidental(string $accidental): void
    {
        if (in_array($accidental, $this::ACCIDENTALS)) {
            $this->accidental = $accidental;
        } else {
            throw new Exception('Accidental is not an appropriate value');
        }
    }

    /**
     * @return int
     */
    public function getOctaveSpn(): int
    {
        return $this->octave_spn;
    }

    /**
     * @param int $octave_spn
     */
    public function setOctaveSpn(int $octave_spn): void
    {
        if ($octave_spn >= 0 && $octave_spn <= 8) {
            $this->octave_spn = $octave_spn;
        } else {
            throw new Exception('Octave SPN is not an appropriate value');
        }
    }
}