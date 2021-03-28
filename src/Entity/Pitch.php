<?php


namespace App\Entity;

use OutOfRangeException;
use JetBrains\PhpStorm\Pure;
use Exception;
use UnexpectedValueException;

/**
 * Pitch class
 */
class Pitch
{
    public const NAMES          = ['F', 'C', 'G', 'D', 'A', 'E', 'B'];
    public const ACCIDENTALS    = ['bbb', 'bb', 'b', 'natural', '#', '##', '###'];
    public const DIRECTIONS     = ['lower', 'raise'];

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
     * If some value is not passed, random is used. Passing empty values results leads to producing random value (within given restrictions).
     * @param string|null $name
     * @param string|null $accidental
     * @param int|null $octave
     * @throws UnexpectedValueException|OutOfRangeException|Exception
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
            $this->validateName($name);
            $this->name = $name;
        }

        if (!$accidental) {
            $this->accidental = $this::ACCIDENTALS[array_rand($this::ACCIDENTALS)];
        } else {
            $this->validateAccidental($accidental);
            $this->accidental = $accidental;
        }

        if (!$octave) {
            $this->octave = random_int(0, 8);
        } else {
            $this->validateOctave($octave);
            $this->octave = $octave;
        }
    }

    /**
     * @return string
     */
    #[Pure] public function __toString(): string
    {
        $acc = $this->getAccidental();
        return
            $this->getName() .
            (($acc === 'natural') ? '' : $acc) .
            $this->getOctave();
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
     * @throws UnexpectedValueException
     */
    public function setName(string $name): void
    {
        $this->validateName($name);
        $this->name = $name;
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
     * @throws UnexpectedValueException
     */
    public function setAccidental(string $accidental): void
    {
        $this->validateAccidental($accidental);
        $this->accidental = $accidental;
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
     * @throws UnexpectedValueException
     */
    public function setOctave(int $octave): void
    {
        $this->validateOctave($octave);
        $this->octave = $octave;
    }

    /**
     * Raises or lowers given pitch up/down an octave (pass direction as either 'raise' or 'lower')
     * @param string $direction
     * @throws OutOfRangeException
     */
    public function moveHalfstep(string $direction): void
    {
        $this->validateDirection($direction);
        $acc = $this->getAccidental();

        $setAcc = function($dir, $sign) use ($direction, $acc) {
            if ($direction === $dir) {
                if (mb_strlen($acc) > 2) {
                    throw new OutOfRangeException('Cannot shift accidental: result exceeds range of triples (###, bbb)');
                } else {
                    $this->setAccidental($acc . $sign);
                }
            } else {
                if (mb_strlen($acc) === 1) {
                    $this->setAccidental('natural');
                } else {
                    $this->setAccidental(substr($acc, 0, -1));
                }
            }
        };

        if ($acc[-1] === '#') {
            $setAcc('raise', '#');
        } else if ($acc[-1] === 'b') {
            $setAcc('lower', 'b');
        } else {
            $this->setAccidental($direction === 'lower' ? 'b' : '#');
        }
    }

    /**
     * @param string $direction
     * @throws OutOfRangeException
     */
    public function moveOctave(string $direction)
    {
        $octave = $this->getOctave();
        $raise = ($direction === 'raise');
        $this->validateDirection($direction);

        if ($octave === ($raise ? 8 : 0)) {
            throw new OutOfRangeException('Cannot shift octave: is out of range (allowed octave range is 0-8)');
        } else {
            $this->setOctave($octave + 1 - ($raise ? 0 : 2));
        }
    }

    /**
     * @param string $direction
     * @return bool
     * @throws UnexpectedValueException
     */
    private function validateDirection(string $direction): bool
    {
        if (!in_array($direction, $this::DIRECTIONS)) {
            throw new UnexpectedValueException('Not a valid direction');
        } else {
            return true;
        }
    }

    /**
     * @param string $accidental
     * @return bool
     * @throws UnexpectedValueException
     */
    private function validateAccidental(string $accidental): bool
    {
        if (!in_array($accidental, $this::ACCIDENTALS)) {
            throw new UnexpectedValueException('Not a valid accidental. 
            Should be either "natural" or sharps/flats (up to 3)');
        } else {
            return true;
        }
    }

    /**
     * @param string $name
     * @return bool
     * @throws UnexpectedValueException
     */
    private function validateName(string $name): bool
    {
        if (!in_array($name, $this::NAMES)) {
            throw new UnexpectedValueException('Not a valid name. Should be a character from A to G.');
        } else {
            return true;
        }
    }

    /**
     * @param int $octave
     * @return bool
     * @throws UnexpectedValueException
     */
    private function validateOctave(int $octave): bool
    {
        if ($octave < 0 || $octave > 8) {
            throw new UnexpectedValueException('Not a valid octave. Should be in the range of 0-8.');
        } else {
            return true;
        }
    }
}