<?php


namespace App\Structures;

use Exception;
use App\Structures\Pitch;

class Scale
{
    const COMMON_DIATONIC_SCALES = [
        'lydian', 'major', 'mixolydian', 'dorian', 'minor', 'phrygian', 'locrian'
    ];

    /**
     * @var array
     */
    private array $pitches;

    /**
     * Construct a scale from some given tonic.
     * N.B.: Intervals are treated as a ditonic scale.
     *
     * @param Pitch $pitch
     * Some pitch you pass as a reference to build other pitches (usually it's the tonic)
     * @param array|string $scale_formula
     * Scale formula contains either strings which represent scale degrees -- e.g. ['1', 'b4']
     * or a string with one of generic scale formulas (see COMMON_DIATONIC_SCALES).
     * @param string $scale_degree_formulaic
     * @throws Exception
     */
    public function __construct(Pitch $pitch, mixed $scale_formula, string $scale_degree_formulaic = '1')
    {
        $map            = [4, 0, 7, 3, 6, 2, 5]; // Bunch of music theory stuff
        $modes          = ['4', '1', '5', '2', '6', '3', '7'];
        $pitch_name     = $pitch->getName();
        $acc            = $pitch->getAccidental();
        $start          = array_search($pitch_name, Pitch::NAMES);
        $f_len          = mb_strlen($scale_degree_formulaic);

        if ($f_len > 1) {
            $scale_degree = $scale_degree_formulaic[-1];
            $check = in_array(mb_substr($scale_degree_formulaic, 0, -1), Pitch::ACCIDENTALS)
                  && in_array($scale_degree, $modes);
        } else {
            $scale_degree = $scale_degree_formulaic;
            $check = in_array($scale_degree, $modes);
        }

        if ($check) {
            $finish = array_search($scale_degree, $modes);
        } else {
            throw new Exception('Passed scale degree is not appropriate');
        }

        $scale = [];
        foreach($modes as $val) {
            $val = (int) $val - 1;
            $scale[] = Pitch::NAMES[$val];
        }
        $scale = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $i      = array_search($pitch_name, $scale);
        $scale  = array_merge(array_slice($scale, $i), array_slice($scale, 0, $i));
        $past_c = false;
        $less = ($start < $finish);

        foreach ($scale as &$name) {
            if ($name === 'C') {
                $past_c = true;
            }
            $name = new Pitch(
                $name,
                'natural',
                $pitch->getOctave() + (($past_c) ? 1 : 0)
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

        $this->setPitches($scale);
        while ($start !== $finish) {
            var_dump($map[$start]); // TODO Пофиксить баг
            if ($map[$start]) {
                $scale[$map[$start] - 1]->moveADiatonicHalfstep($less ? 'lower' : 'raise');
            } else {
                var_dump('xxx');
            }
            $less ? ++$start : --$start;
        }
        if ($acc !== 'natural') {
            foreach($scale as $p) {
                $p->moveADiatonicHalfstep($acc === '#' ? 'raise' : 'lower');
            }
        }
        $this->setPitches($scale);
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