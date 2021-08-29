<?php


namespace App\Entity;

use Exception;
use App\DTO\PitchDTO;
use App\Helper\ArrayHelper;
use JetBrains\PhpStorm\Pure;
use UnexpectedValueException;

/**
 * Scale class
 */
class Scale
{
    public const COMMON_SCALES = [
        'lydian'            => ['1', '2', '3', '#4', '5', '6', '7'],
        'major'             => ['1', '2', '3', '4', '5', '6', '7'],
        'mixolydian'        => ['1', '2', '3', '4', '5', '6', 'b7'],
        'dorian'            => ['1', '2', 'b3', '4', '5', '6', 'b7'],
        'minor'             => ['1', '2', 'b3', '4', '5', 'b6', 'b7'],
        'phrygian'          => ['1', 'b2', 'b3', '4', '5', 'b6', 'b7'],
        'locrian'           => ['1', 'b2', 'b3', '4', 'b5', 'b6', 'b7'],
        'melodic minor'     => ['1', '2', 'b3', '4', '5', '6', '7'],
        'harmonic minor'    => ['1', '2', 'b3', '4', '5', 'b6', '7']
    ];

    /**
     * @var array
     */
    private array $pitches;

    /**
     * Construct a scale from some given tonic.
     * N.B.: Intervals are treated as a ditonic scale.
     *
     * @param Pitch|null $pitch
     * Some pitch you pass as a reference to build other pitches (usually it's the tonic). Random by default.
     * @param string|null $scale_formula
     * Scale formula contains either strings which represent scale degrees -- e.g. 'b3,5,1'
     * or a string with one of generic scale formulas (see COMMON_DIATONIC_SCALES). Major by default.
     * @param string|null $scale_degree_formulaic
     * Denotes which scale degree you've passed as a pitch (e.g. 'b3'). Tonic by default.
     * @throws Exception
     */
    public function __construct(?Pitch $pitch, string|null $scale_formula = 'major', ?string $scale_degree_formulaic = '1')
    {
        if (is_null($pitch)) {
            $pitch = new Pitch;
        }

        if (is_null($scale_formula)) {
            $scale_formula = 'major';
        }

        if (is_null($scale_degree_formulaic)) {
            $scale_degree_formulaic = '1';
        }

        $map            = [4, 0, 7, 3, 6, 2, 5]; // Bunch of music theory stuff
        $modes          = ['4', '1', '5', '2', '6', '3', '7'];
        $pitch_name     = $pitch->getName();
        $acc            = $pitch->getAccidental();
        $oct            = $pitch->getOctave();
        $start          = array_search($pitch_name, Pitch::NAMES);

        $process_formulaic = function ($scale_degree_formulaic) use ($modes) {
            if (mb_strlen($scale_degree_formulaic) > 1) {
                $scale_degree = $scale_degree_formulaic[-1];
                $check = in_array($f_acc = mb_substr($scale_degree_formulaic, 0, -1), Pitch::ACCIDENTALS)
                    && in_array($scale_degree, $modes);
            } else {
                $scale_degree = $scale_degree_formulaic;
                $check = in_array($scale_degree, $modes);
                $f_acc = 'natural';
            }
            if ($check) {
                $finish = array_search($scale_degree, $modes);
            } else {
                throw new UnexpectedValueException('Passed value (either formula or degree) is invalid. Check on documentation.');
            }
            return [$scale_degree, $f_acc, $finish];
        };

        [$scale_degree, $f_acc, $finish] = $process_formulaic($scale_degree_formulaic);

        $scale  = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $i      = array_search($pitch_name, $scale);
        $scale  = ArrayHelper::rearrangeFromIndex($scale, $i);
        $less   = ($start < $finish);

        foreach ($scale as &$name) {
            $name = new Pitch(
                $name,
                'natural',
                4
            );
        }

        // If the pitch is G, $scale (as for now) contains G mixolydian scale; B -- B locrian scale, etc.
        //
        // Now we need to move from start to finish in $map,
        // lowering (in case of moving to the right) or raising (to the left) the pitches
        // in our blank scale as indexed in $map to obtain the necessary mode.
        //
        // For example, moving towards 0 instead of finish index in $map in any case would give us
        // a corresponding major scale for given tonic

        while ($start !== $finish) {
            if ($less) {
                $start++;
            }
            $index = $map[$start];
            if ($index) {
                /** @var Pitch $p */
                $p = $scale[$index - 1];
                $p->moveHalfstep($less ? 'lower' : 'raise');
            }
            if (!$less) {
                $start--;
            }
        }

        if ($scale_degree === '4' || $scale[0]->getName() === 'F') {
            /** @var Pitch $lydian */
            $lydian = $scale[3];
            $lydian->moveHalfstep('lower');
        }

        $shift_pitch = function(Pitch $p, string $acc, array $order_array) {
            $p = clone $p;
            if ($acc !== 'natural') {
                $i = mb_strlen($acc);
                while ($i--) {
                    $p->moveHalfstep($acc[-1] === '#' ? $order_array[0] : $order_array[1]);
                }
            }
            return $p;
        };

        $shift_scale = function(array $scale, string $acc, array $order_array) use ($shift_pitch) {
            $a = [];
            foreach($scale as &$p) {
                $a[] = $shift_pitch($p, $acc, $order_array);
            }
            return $a;
        };

        $this->setPitches($scale);

        $scale = $shift_scale($scale, $f_acc, ['lower', 'raise']);
        $scale = $shift_scale($scale, $acc, ['raise', 'lower']);

        $scale = (ArrayHelper::rearrangeFromIndex($scale, 8 - (int) $scale_degree));
        $scale_basic = array_map(function($el) {return $el->getName();}, $scale);

        // Now, since we've constructed our major scale, we can actually apply our scale formula

        if (in_array($scale_formula, array_keys(self::COMMON_SCALES))) {
            $scale_formula = self::COMMON_SCALES[$scale_formula];
        } else {
            if (is_string($scale_formula)) {
                $scale_formula = explode(',', $scale_formula);
            }
        }

        $apply_formula = function (array $scale, array $formula) use ($process_formulaic, $shift_pitch) {
            $output_pitches = [];
            foreach ($formula as $element) {
                [$scale_degree, $f_acc] = $process_formulaic($element);
                $output_pitches[] = $shift_pitch($scale[(int) $scale_degree - 1], $f_acc, ['raise', 'lower']);
            }
            return $output_pitches;
        };

        $scale      = $apply_formula($scale, $scale_formula);
        $octaves    = [0];
        $shifts     = [];
        $c_ind      = array_search('C', $scale_basic);
        $switch     = 0;

        $check_octave = function (int $degree, int $next_degree, int $c_ind, int $is_c) use ($scale_basic, &$switch) {
            if (!$is_c) {
                if ($degree < $next_degree) {
                    if ($c_ind > $degree - 1 && $c_ind <= $next_degree - 1) {
                        return true;
                    }
                } elseif ($degree > $next_degree) {
                    if ($c_ind > $degree - 1 && $c_ind < 7 || $c_ind >= 0 && $c_ind <= $next_degree - 1) {
                        $switch++;
                        return true;
                    }
                }
            }
            if ($degree === $next_degree) {
                return true;
            }
            return false;
        };

        for ($i = 0; $i < count($scale_formula) - 1; $i++) {
            $degree         = (int) $scale_formula[$i][-1];
            $next_degree    = (int) $scale_formula[$i + 1][-1];
            $is_c           = ($degree - 1 === $c_ind);
            if ($next_octave = $check_octave($degree, $next_degree, $c_ind, $is_c)) {
                $shifts[] = $i + 1;
            }
            $octaves[$i + 1] = $octaves[$i] + (int) $next_octave;
        }

        $shifts = (count($shifts)) ? $shifts[intdiv(count($shifts), 2)] : 0;
        $target = ($switch) ? $octaves[$shifts] : 0;
        $dir    = ($target < $oct) ? 1 : -1;

        if ((int) $scale_degree_formulaic - 1 > $c_ind) {
            $oct -= 1;
        }

        while ($target !== $oct) {
            foreach ($octaves as &$o) {
                $o += $dir;
            }
            $target += $dir;
        }

        for ($i = 0; $i < count($octaves); $i++) {
            /** @var Pitch $p */
            $p = $scale[$i];
            $p->setOctave($octaves[$i]);
        }

        $this->setPitches($scale);
    }

    /**
     * @return string
     */
    #[Pure] public function __toString(): string
    {
        $string = '';
        foreach ($this->pitches as $pitch) {
            $string .= ((string) $pitch) . ' ';
        }
        return rtrim($string);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->pitches as $pitch) {
            $array[] = (string) $pitch;
        }
        return $array;
    }

    /**
     * @return array
     */
    public function getPitches(): array
    {
        return $this->pitches;
    }

    /**
     * @param array $pitches
     */
    private function setPitches(array $pitches): void
    {
        $this->pitches = $pitches;
    }
}