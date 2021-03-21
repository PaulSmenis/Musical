<?php


namespace App\Structures;
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
     * @return string
     */
    #[Pure] public function getPitchClass(): string
    {
        return $this->getName() . $this->getAccidental();
    }

    /**
     * Expecting sane values in a musical sense (F instead of E#, A instead of G##, etc.)
     * @param string $direction
     * @param int $times
     */
    public function moveADiatonicHalfstep(string $direction, int $times = 1): void
    {
        $name           = $this->getName();
        $acc            = $this->getAccidental();
        $up_no_acc      = in_array($name, ['B', 'E']);
        $down_no_acc    = in_array($name, ['C', 'F']);

        $this->validateDirection($direction);

        if (
            !in_array($acc, ['#', 'b', 'natural'])
            || $acc === '#' && $up_no_acc
            || $acc === 'b' && $down_no_acc) {
            throw new Exception('Pass an appropriate value');
        } else {
            $raise = ($direction === 'raise');
            $c = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
            $i = array_search($name, $c) + 1 - (($raise) ? 0 : 1);
            while ($times) {
                if ($i < 0) {
                    $i += 7;
                    $this->moveOctave('lower');
                } else if ($i > 6) {
                    $i -= 7;
                    $this->moveOctave('raise');
                }
                $name = $c[$i];

                if ($acc === 'natural') {
                    if ($raise && $up_no_acc || !$raise && $down_no_acc) {
                        $this->setName($name);
                    } else {
                        $this->setAccidental($raise ? '#' : 'b');
                    }
                } else {
                    if ($acc === '#' ? $raise : !$raise) {
                        $this->setName($name);
                    }
                    $this->setAccidental('natural');
                }
                $times--;
            }
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
     */
    public function moveAccidental(string $direction)
    {
        $raise = ($direction === 'raise');
        $this->validateDirection($direction);
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