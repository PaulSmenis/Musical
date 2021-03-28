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
    /**
     * Tests pitch name constructor validation
     */
    public function testNameConstructorException()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('K', '#', 2);
    }

    /**
     * Tests pitch accidental constructor validation
     */
    public function testAccidentalConstructorException()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('D', '####', 2);
    }

    /**
     * Tests pitch octave constructor validation
     */
    public function testOctaveConstructorException()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('A', 'b', 9);
    }

    /**
     * Tests pitch name setter validation
     */
    public function testNameSetterException()
    {
        $this->expectException(UnexpectedValueException::class);
        (new Pitch)->setName('not valid bro');
    }

    /**
     * Tests pitch accidental setter validation
     */
    public function testAccidentalSetterException()
    {
        $this->expectException(UnexpectedValueException::class);
        (new Pitch)->setAccidental('####');
    }

    /**
     * Tests pitch octave setter validation
     */
    public function testOctaveSetterException()
    {
        $this->expectException(UnexpectedValueException::class);
        (new Pitch)->setOctave(-1);
    }

    /**
     * Tests pitch name constructor validation (empty string case)
     */
    public function testNameConstructorEmptyStringException()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('', 'b', 3);
    }

    /**
     * Tests pitch accidental constructor validation (empty string case)
     */
    public function testAccidentalConstructorEmptyStringException()
    {
        $this->expectException(UnexpectedValueException::class);
        new Pitch('C', '', 5);
    }
}