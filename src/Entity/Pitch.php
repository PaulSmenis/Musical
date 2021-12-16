<?php


namespace App\Entity;

use Exception;
use OutOfBoundsException;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pitch class
 */
class Pitch
{
    public const NAMES          = ['F', 'C', 'G', 'D', 'A', 'E', 'B'];
    public const ACCIDENTALS    = ['bbb', 'bb', 'b', 'natural', '#', '##', '###'];
    public const ACCIDENTALS_UPWARDS = ['#', '##', '###'];
    public const ACCIDENTALS_DOWNWARDS = ['bbb', 'bb', 'b'];
    public const DIRECTIONS     = ['lower', 'raise'];

    /**
     * Pitch name (e.g. G).
     * @var string $name
     */
    private string $name;

    /**
     * Pitch accidental (e.g. #); Triples at max.
     *
     * @var string $accidental
     */
    private string $accidental;

    /**
     * Pitch octave (e.g. 3); 0-8 are valid (SPN).
     *
     * @var int $octave
     */
    private int $octave;

    /**
     * Pitch constructor.
     *
     * If some value is not passed or is null, random is used.
     * If name is 'default', F4 is returned.
     * Passing empty values results leads to producing random value (within given restrictions).
     * @param string|null $name
     * @param string|null $accidental
     * @param int|null $octave
     * @throws UnexpectedValueException|OutOfBoundsException|Exception
     */
    public function __construct(
        ?string $name = null,
        ?string $accidental = null,
        ?int $octave = null
    )
    {
        if (is_null($name)) {
            $this->name = $this::NAMES[array_rand($this::NAMES)];
        } elseif ($name === 'default') {
            $this->name = 'F';
            $this->accidental = 'natural';
            $this->octave = 4;
            return;
        } else {
            $this->validateName($name);
            $this->name = $name;
        }

        $needsCorrectionOnRandomAccidental = $accidental === null && $octave !== null;
        $needsCorrectionOnRandomName = $accidental !== null && $octave !== null && $name === null;

        $this->setAccidental($accidental, true);
        $this->setOctave($octave, true);

        if ($needsCorrectionOnRandomAccidental) {
            $this->modifyAccidentalToNotExceedRange();
        }
        if ($needsCorrectionOnRandomName) {
            $this->modifyNameToNotExceedRange();
        }

        $this->validateRange();
    }

    public static function getAccidentalsWithoutBordercases(): array
    {
        $return = array_filter(Pitch::ACCIDENTALS, function(string $accidental) {return mb_strlen($accidental) < Pitch::getMaxAccidentalLength();});
        $return[] = 'natural';
        return $return;
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
     * @param string|null $name
     * @throws OutOfBoundsException|UnexpectedValueException
     */
    public function setName(?string $name): void
    {
        if (is_null($name)) {
            $this->name = $this::NAMES[array_rand($this::NAMES)];
            $this->modifyNameToNotExceedRange();
        } else {
            $this->validateName($name);
            $this->name = $name;
            $this->validateRange();
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
     * @param string|null $accidental
     * @param bool $avoid_validation
     * @throws OutOfBoundsException|UnexpectedValueException
     */
    public function setAccidental(?string $accidental, bool $avoid_validation = false): void
    {
        if (is_null($accidental)) {
            $this->accidental = $this::ACCIDENTALS[array_rand(Pitch::getAccidentalsWithoutBordercases())];
            $this->modifyAccidentalToNotExceedRange();
        } else {
            $this->validateAccidental($accidental);
            $this->accidental = $accidental;
            if (!$avoid_validation) {
                $this->validateRange();
            }
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
     * @param int|null $octave
     * @param bool $avoid_validation
     * @throws OutOfBoundsException|UnexpectedValueException
     */
    public function setOctave(?int $octave, bool $avoid_validation = false): void
    {
        if (is_null($octave)) {
            $this->octave = random_int(0, 8);
            $this->modifyOctaveToNotExceedRange();
        } else {
            $this->validateOctave($octave);
            $this->octave = $octave;
            if (!$avoid_validation) {
                $this->validateRange();
            }
        }
    }

    /**
     * Raises or lowers given pitch up/down a halfstep (pass direction as either 'raise' or 'lower').
     *
     * @param string $direction
     * @param int|null $halfsteps
     * @throws OutOfBoundsException
     */
    public function moveHalfstep(string $direction, ?int $halfsteps = 1): void
    {
        while ($halfsteps) {
            $this->validateDirection($direction);
            $acc = $this->getAccidental();

            $setAcc = function($dir, $sign) use ($direction, $acc) {
                if ($direction === $dir) {
                    if (mb_strlen($acc) > Pitch::getMaxAccidentalLength() - 1) {
                        throw new OutOfBoundsException('Cannot shift accidental: result exceeds maximum range.');
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

            $this->validateRange();

            $halfsteps--;
        }
    }

    /**
     * Raises or lowers given pitch up/down an octave (pass direction as either 'raise' or 'lower').
     *
     * @param string $direction
     * @param int|null $octaves
     * @throws OutOfBoundsException
     */
    public function moveOctave(string $direction, ?int $octaves = 1)
    {
        while ($octaves) {
            $octave = $this->getOctave();
            $raise = ($direction === 'raise');
            $this->validateDirection($direction);

            if ($octave === ($raise ? 8 : 0)) {
                throw new OutOfBoundsException('Cannot shift octave: is out of range (allowed octave range is 0-8).');
            } else {
                $this->setOctave($octave + 1 - ($raise ? 0 : 2));
            }

            $this->validateRange();
            $octaves--;
        }
    }

    /**
     * @param string $direction
     * @return void
     * @throws UnexpectedValueException
     */
    private function validateDirection(string $direction): void
    {
        if (!in_array($direction, $this::DIRECTIONS)) {
            throw new UnexpectedValueException('Not a valid direction');
        }
    }

    /**
     * @param string $accidental
     * @return void
     * @throws UnexpectedValueException
     */
    private function validateAccidental(string $accidental): void
    {
        if (!in_array($accidental, $this::ACCIDENTALS)) {
            throw new UnexpectedValueException('Not a valid accidental. Should be either natural or sharps/flats (up to ' . Pitch::getMaxAccidentalLength() . ')');
        }
    }

    /**
     * @param string $name
     * @return void
     * @throws UnexpectedValueException
     */
    private function validateName(string $name): void
    {
        if (!in_array($name, $this::NAMES)) {
            throw new UnexpectedValueException('Not a valid name. Should be a character from A to G.');
        }
    }

    /**
     * @param int $octave
     * @return void
     * @throws UnexpectedValueException
     */
    private function validateOctave(int $octave): void
    {
        if ($octave < 0 || $octave > 8) {
            throw new UnexpectedValueException('Not a valid octave. Should be in the range of 0-8.');
        }
    }

    /**
     * @return void
     * @throws OutOfBoundsException
     */
    private function validateRange(): void
    {
        if ($this->ifExceedsRangeFromBelow()) {
            throw new OutOfBoundsException('Pitch is out of range from below. Cannot be lower than C0.');
        }
    }

    /**
     * Checks if combination of given pitch name, octave and accidental puts it below reasonable limit (which is C0).
     *
     * @return bool
     */
    private function ifExceedsRangeFromBelow(): bool
    {
        if (!isset($this->octave) || $this->octave !== 0) {
            return false;
        }
        if ($this->name === 'C' && in_array($this->accidental, $this::ACCIDENTALS_DOWNWARDS)
            ||
            $this->name === 'D' && $this->accidental === 'bbb') {
            return true;
        } else {
            return false;
        }
    }

    private function modifyAccidentalToNotExceedRange(): void
    {
        if ($this->ifExceedsRangeFromBelow()) {
            if ($this->name === 'D') {
                $this->moveHalfstep('raise');
            } elseif ($this->name === 'C') {
                $this->setAccidental('natural');
            }
        }
    }

    private function modifyNameToNotExceedRange(): void
    {
        if ($this->ifExceedsRangeFromBelow()) {
            if ($this->name === 'C' && $this->accidental !== 'bbb') {
                $this->setName('D');
            } elseif ($this->name === 'D' || $this->name === 'C' && $this->accidental === 'bbb') {
                $this->setName('E');
            }
        }
    }

    private function modifyOctaveToNotExceedRange(): void
    {
        if ($this->ifExceedsRangeFromBelow()) {
            $this->octave = 1;
        }
    }

    /**
     * Validates pitch array in scale and pitch constructor (array is an optional way for passing pitch).
     *
     * @param array $pitchArray
     * @throws UnexpectedValueException
     */
    public static function validatePitchArray(array $pitchArray)
    {
        if (count($pitchArray) !== 3) { // Name, accidental and octave should be in passed array
            throw new UnexpectedValueException('Passed array of pitch parameters is invalid.');
        } else {
            if (array_keys($pitchArray) !== ['name', 'accidental', 'octave']) {
                throw new UnexpectedValueException('Passed array of pitch parameters is invalid.');
            } else {
                Pitch::validatePitchDataTypes(...array_values($pitchArray));
            }
        }
    }

    /**
     * Validates data types of pitch parameters passed in the request.
     *
     * @param $name
     * @param $accidental
     * @param $octave
     * @throws UnexpectedValueException
     */
    public static function validatePitchDataTypes($name, $accidental, $octave): void
    {
        if (!is_null($name) && !is_string($name)) {
            throw new UnexpectedValueException('Incorrect name data type (available: null|string).');
        } elseif (!is_null($accidental) && !is_string($accidental)) {
            throw new UnexpectedValueException('Incorrect accidental data type (available: null|string).');
        } elseif (!is_null($octave) && !is_int($octave)) {
            throw new UnexpectedValueException('Incorrect octave data type (available: null|int).');
        }
    }

    public static function getMaxAccidentalLength(): int
    {
        return max(array_map(function (string $accSymbol) {return mb_strlen($accSymbol);}, array_merge(Pitch::ACCIDENTALS_DOWNWARDS, Pitch::ACCIDENTALS_UPWARDS)));
    }

    public function movePitchName(string $dir): void
    {
        $ordered_pitch_names = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $i = array_search($this->name, $ordered_pitch_names);
        if ($dir === 'raise') {
            if ($i === 6) {
                $this->setName('C');
            } else {
                $this->setName($ordered_pitch_names[$i + 1]);
            }
        } elseif ($dir === 'lower') {
            if ($i === 0) {
                $this->setName('B');
            } else {
                $this->setName($ordered_pitch_names[$i - 1]);
            }
        } else {
            // TODO Ексепшн
        }
    }
}