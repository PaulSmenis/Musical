<?php


namespace App\Tests\Unit\Entity\Scale;

use App\Entity\Pitch;
use App\Entity\Scale;
use PHPUnit\Framework\TestCase;
use Throwable;
use UnexpectedValueException;

/**
 * Scale entity unit testing class
 * @package App\Tests\Unit\Entity\Scale
 */
class ScaleTest extends TestCase
{
    /**
     * Tests empty scale formula constructor validation
     */
    public function testFormulaExceptionEmpty()
    {
        $this->expectException(UnexpectedValueException::class);
        $scale = (string) new Scale(
            new Pitch('C', 'natural', 4),
            '',
            '1'
        );
    }

    /**
     * Tests whether scale constructor works adequately to passed parameters or not
     */
    public function testConstruct()
    {
        $scale = (string) new Scale(
            new Pitch('C', 'natural', 4),
            'major',
            '1'
        );
        $this->assertEquals('C4 D4 E4 F4 G4 A4 B4', $scale);

        $scale = (string) new Scale(
            new Pitch('G', 'b', 3),
            'minor',
            '1'
        );
        $this->assertEquals('Gb3 Ab3 Bbb3 Cb4 Db4 Ebb4 Fb4', $scale);

        $scale = (string) new Scale(
            new Pitch('B', '#', 4),
            'dorian',
            '3'
        );
        $this->assertEquals('G#4 A#4 B4 C#5 D#5 E#5 F#5', $scale);

        $scale = (string) new Scale(
            new Pitch('D', 'b', 3),
            '2,b1',
            '5'
        );
        $this->assertEquals('Ab1 Gbb2', $scale);

        $scale = (string) new Scale(
            new Pitch('A', 'natural', 3),
            'mixolydian',
            '3'
        );
        $this->assertEquals('F3 G3 A3 Bb3 C4 D4 Eb4', $scale);

        $scale = (string) new Scale(
            new Pitch('C', 'natural', 3),
            '5,1',
            '1'
        );
        $this->assertEquals('G2 C3', $scale);

        $scale = (string) new Scale(
            new Pitch('B', '#', 5),
            '5,b3,1',
            '#2'
        );
        $this->assertEquals('E4 C5 A5', $scale);

        $scale = (string) new Scale(
            new Pitch('E', 'b', 2),
            '4,1',
            '1'
        );
        $this->assertEquals('Ab1 Eb2', $scale);

        $scale = (string) new Scale(
            new Pitch('E', 'b', 2),
            '4,1',
            '4'
        );
        $this->assertEquals('Eb0 Bb1', $scale);

        $scale = (string) new Scale(
            new Pitch('G', 'natural', 4),
            '4,2',
            '2'
        );
        $this->assertEquals('Bb3 G4', $scale);

        $scale = (string) new Scale(
            new Pitch('B', 'b', 3),
            '1,6',
            '1'
        );
        $this->assertEquals('Bb3 G4', $scale);

        $scale = (string) new Scale(
            new Pitch('A', 'natural', 3),
            '3,1,b2,5,7',
            '1'
        );
        $this->assertEquals('C#3 A3 Bb3 E4 G#4', $scale);

        $scale = (string) new Scale(
            new Pitch('A', 'b', 3),
            'major',
            'bb6'
        );
        $this->assertEquals('C#3 D#3 E#3 F#3 G#3 A#3 B#3', $scale);
    }
}