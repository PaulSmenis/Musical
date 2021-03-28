<?php


namespace App\Entities;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Config\Definition\Exception\Exception;

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
    #[Pure] public function __toString(): string
    {
        $acc = $this->getAccidental();
        return
            $this->getName() .
            (($acc === 'natural') ? '' : $acc) .
            $this->getOctave();
    }

    public function __clone()
    {

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
        if (in_array($name, $this::NAMES)) {
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
        if ($this->validateAccidental($accidental)) {
            $this->accidental = $accidental;
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

    /**
     * @param string $direction
     * @param int $times
     */
    public function moveHalfstep(string $direction): void
    {
        $this->validateDirection($direction);
        $acc = $this->getAccidental();

        $setAcc = function($dir, $sign) use ($direction, $acc) {
            if ($direction === $dir) {
                if (mb_strlen($acc) > 2) {
                    throw new Exception('Accidental exceeds range of triples');
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
     */
    public function moveOctave(string $direction)
    {
        $octave = $this->getOctave();
        $raise = ($direction === 'raise');
        $this->validateDirection($direction);

        if ($octave === ($raise ? 8 : 0)) {
            throw new Exception('Octave is out of range');
        } else {
            $this->setOctave($octave + 1 - ($raise ? 0 : 2));
        }
    }

    /**
     * @param string $direction
     * @return bool
     */
    private function validateDirection(string $direction): bool
    {
        if (!in_array($direction, $this::DIRECTIONS)) {
            throw new Exception('Not a valid direction');
        } else {
            return true;
        }
    }

    /**
     * @param string $accidental
     * @return bool
     */
    private function validateAccidental(string $accidental): bool
    {
        if (!in_array($accidental, $this::ACCIDENTALS)) {
            throw new Exception('Not a valid accidental');
        } else {
            return true;
        }
    }

}