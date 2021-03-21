<?php


namespace App\Structures;
use Symfony\Component\Config\Definition\Exception\Exception;

class Pitch
{
    public const NAMES = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
    public const ACCIDENTALS = ['###', '##', '#', 'bbb', 'bb', 'b', 'natural'];

    /**
     * @var string $name
     */
    private string $name;

    /**
     * @var string $accidental
     */
    private string $accidental;

    /**
     * @var int $octave
     */
    private int $octave;

    /**
     * Pitch constructor.
     *
     * If some value is not passed, random is used.
     * @param string|null $name
     * @param string|null $accidental
     * @param int|null $octave
     * @throws \Exception
     */
    public function __construct(
        string $name = null,
        string $accidental = null,
        int $octave = null
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

        if (!$octave) {
            $this->octave = random_int(0, 8);
        } else {
            $this->octave = $octave;
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
    public function getOctave(): int
    {
        return $this->octave;
    }

    /**
     * @param int $octave
     */
    public function setOctave(int $octave): void
    {
        if ($octave >= 0 && $octave <= 8) {
            $this->octave = $octave;
        } else {
            throw new Exception('Octave SPN is not an appropriate value');
        }
    }
}