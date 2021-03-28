<?php


namespace App\Tests\Unit\Entity\Pitch;

use App\Entity\Pitch;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Pitch entity unit testing class
 * @package App\Tests\Unit\Entity\Pitch
 */
class PitchTest extends TestCase
{
    public function testNameConstructorValidation()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('K', '#', 2);
    }

    public function testAccidentalConstructorValidation()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('D', '####', 2);
    }

    public function testOctaveConstructorValidation()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('A', 'b', 9);
    }

    public function testNameSetterValidation()
    {
        $this->expectException(UnexpectedValueException::class);
        (new Pitch)->setName('not valid bro');
    }

    public function testAccidentalSetterValidation()
    {
        $this->expectException(UnexpectedValueException::class);
        (new Pitch)->setAccidental('####');
    }

    public function testOctaveSetterValidation()
    {
        $this->expectException(UnexpectedValueException::class);
        (new Pitch)->setOctave(-1);
    }
}