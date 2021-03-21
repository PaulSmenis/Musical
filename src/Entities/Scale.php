<?php


namespace App\Structures;

use Nelmio\ApiDocBundle\Annotation\Model;


class Scale
{
    const COMMON_DIATONIC_SCALES = [
        'major', 'dorian', 'phrygian', 'lydian', 'mixolydian', 'minor', 'locrian'
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
     * Some pitch you pass as a reference to build others (usually it's the tonic)
     * @param array|string $scale_formula
     * Scale formula contains either strings which represent scale degrees -- e.g. ['1', 'b4']
     * or a string with one of generic scale formulas (see COMMON_DIATONIC_SCALES).
     * @param string $scale_degree
     * Defines your passed pitch in relationship to the formula.
     * This approach basically allows to build descending intervals
     */
    public function __construct(Pitch $pitch, mixed $scale_formula, string $scale_degree = '1')
    {

    }
}