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

    public function testConstruct()
    {
        $scale = (string) new Scale(
            new Pitch('C', 'natural', 4),
            'major',
            '1'
        );
        $this->assertEquals('C4 D4 E4 F4 G4 A4 B4', $scale);
    }
}