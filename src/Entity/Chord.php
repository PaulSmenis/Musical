<?php


namespace App\Entity;


use JetBrains\PhpStorm\Pure;

class Chord
{
    public const COMMON_CHORD_QUALITIES = [
        'sus4'            => ['1', '4', '5'],
        'sus2'            => ['1', '2', '5'],
        'aug'             => ['1', '3', '#5'],
        ''                => ['1', '3', '5'],
        'm'               => ['1', 'b3', '5'],
        'lyd'             => ['1', '#4', '5'],
        'loc'             => ['1', 'b2', 'b5'],
        'dim'             => ['1', 'b3', 'b5'],
        'm7'              => ['1', 'b3', '5', 'b7'],
        'maj7'            => ['1', '3', '5', '7'],
        '7'               => ['1', '3', '5', 'b7'],
        'm7b5'            => ['1', 'b3', 'b5', 'b7'],
        'dim7'            => ['1', 'b3', 'b5', 'bb7'],
        '7#5'             => ['1', '3', '#5', 'b7'],
        '7b5'             => ['1', '3', 'b5', 'b7'],
        'phr'             => ['1', 'b2', '5'],
        '5'               => ['1', '5']
    ];

    /**
     * Actual chord structure (pitchwise)
     * @var Scale $scale
     */
    private Scale $scale;

    /**
     * Chord quality (5, sus4, maj7, etc.)
     * @var string $quality
     */
    private string $quality;

    /**
     * Chord formula (e.g [b3, 1, 5])
     * @var array
     */
    private array $formula;

    /**
     * Chord inversion.
     * @var int $inversion
     */
    private int $inversion;

    /**
     * @throws \Exception
     */
    public function __construct(
        ?Pitch $pitch = null, // TODO Сделать, как в Scale -- возможность выбора массива
        ?string $quality = '',
        ?int $inversion = 0 // 0 means 'root'
    )
    {
        if (in_array($quality, ['M'])) {
            $quality = '';
        }

        if ($pitch === null) {
            $pitch = new Pitch;
        }

        $qualities_keys = array_keys(self::COMMON_CHORD_QUALITIES);
        if ($quality === null) {
            $quality = $qualities_keys[array_rand($qualities_keys)];
        }
        $this->setQuality($quality);

        $quality_formula = self::COMMON_CHORD_QUALITIES[$quality];
        $this->setFormula($quality_formula);

        $len = count($quality_formula) - 1;
        if ($inversion === null) {
            $inversion = random_int(0, $len);
        }
        $this->setScale(new Scale($pitch, $this->formula));
        $this->setInversion($inversion);
    }

    /**
     * @return Scale
     */
    public function getScale(): Scale
    {
        return $this->scale;
    }

    /**
     * @param Scale $scale
     */
    private function setScale(Scale $scale): void
    {
        $this->scale = $scale;
    }

    /**
     * @return string
     */
    public function getQuality(): string
    {
        return $this->quality;
    }

    /**
     * @param string $quality
     */
    private function setQuality(string $quality): void
    {
        if (!in_array($quality, array_keys(Chord::COMMON_CHORD_QUALITIES))) {
            throw new \UnexpectedValueException('Invalid chord quality has been passed.');
        } else {
            $this->quality = $quality;
        }
    }

    /**
     * @throws \Exception
     */
    public function setInversion(int $inversion)
    {
        if ($inversion < 0 || $inversion > count($this->formula) - 1) {
            throw new \UnexpectedValueException('Invalid chord inversion has been passed.');
        } else {
            $quality_nums = array_map(function ($quality) {return (int) $quality[-1];}, $this->formula);
            $sorted_quality_nums = $quality_nums;
            asort($sorted_quality_nums);
            $val = $this->formula[array_keys($sorted_quality_nums)[$inversion]];
            $tonic = $this->getTonic();
            $formula = $this->formula;
            while ($formula[0] !== $val) {
                $el = array_shift($formula);
                array_push($formula, $el);
            }
            $this->formula = $formula;
            $scale = new Scale($tonic, $formula);
            $this->setScale($scale);
            $this->inversion = $inversion;
        }
    }

    #[Pure] public function __toString(): string
    {
        $tonic = $this->getTonic();
        $acc = $tonic->getAccidental();
        $name = $tonic->getName() . ($acc !== 'natural' ? $acc : '') . $this->quality;
        if ($this->inversion !== 0) {
            $inversion_text = ["1st", "2nd", "3rd", "4th"][$this->inversion - 1];
            $name .= " ({$inversion_text} inversion)";
        } else {
            $name .= " (root)";
        }
        return $name;
    }

    /**
     * @return Pitch
     */
    #[Pure] public function getTonic(): Pitch
    {
        return $this->scale->getPitches()[array_search('1', $this->formula)];
    }

    /**
     * @return array
     */
    public function getFormula(): array
    {
        return $this->formula;
    }

    /**
     * @param array $formula
     */
    private function setFormula(array $formula): void
    {
        $this->formula = $formula;
    }

    /**
     * @return int
     */
    public function getInversion(): int
    {
        return $this->inversion;
    }
}