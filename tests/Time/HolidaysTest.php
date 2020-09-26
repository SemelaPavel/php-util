<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use SemelaPavel\Time\Holidays;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class HolidaysTest extends TestCase
{
    protected $holidays;
    
    const NEW_YEARS_DAY_DATE = "2020-01-01";
    const NEW_YEARS_DAY_DESCRIPTION = "New Years's Day";
    const CHRISTMAS_EVE_DATE = "2020-12-24";
    const EMPTY_DESCRIPTION = "";
    const NOT_IN_ARRAY_KEY = "2019-01-01";
        
    protected function setUp(): void
    {
        $this->holidays = new Holidays();
        
        $this->holidays[
            self::NEW_YEARS_DAY_DATE
        ] = self::NEW_YEARS_DAY_DESCRIPTION;
        
        $this->holidays[
            new \DateTime(self::CHRISTMAS_EVE_DATE)
        ] = self::EMPTY_DESCRIPTION;
    }
    
    public function testEaster()
    {
        $this->expectException(\InvalidArgumentException::class);
        $easter = new \DateTimeImmutable("2020-04-12");
        $this->assertEquals($easter, Holidays::easter("2020"));
        $this->assertNotEquals($easter, Holidays::easter("2019"));
        Holidays::easter(" ");
    }

    public function testGoodFriday()
    {
        $this->expectException(\InvalidArgumentException::class);
        $goodFriday = new \DateTimeImmutable("2020-04-10");
        $this->assertEquals($goodFriday, Holidays::goodFriday("2020"));
        $this->assertNotEquals($goodFriday, Holidays::goodFriday("2019"));
        Holidays::goodFriday(null);
    }
    
    public function testEasterMonday()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $easterMonday = new \DateTimeImmutable("2020-04-13");
        $this->assertEquals($easterMonday, Holidays::easterMonday("2020"));
        $this->assertNotEquals($easterMonday, Holidays::easterMonday("2019"));
        
        Holidays::goodFriday(" 789");
    }
    
    public function testOffsetSetWrongFormatException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->holidays['2020.01.01'] = 'Wrong formated date string!';
    }
    
    public function testOffsetSetEmptyArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->holidays[] = 'Not a valid date string!';
    }
    
    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->holidays[self::NEW_YEARS_DAY_DATE]));
        $this->assertTrue(isset($this->holidays[self::CHRISTMAS_EVE_DATE]));
        
        $this->assertTrue(
            isset($this->holidays[new \DateTime(self::NEW_YEARS_DAY_DATE)])
        );
        $this->assertTrue(
            isset($this->holidays[new \DateTime(self::CHRISTMAS_EVE_DATE)])
        );
        
        $this->assertFalse(isset($this->holidays[self::NOT_IN_ARRAY_KEY]));
        $this->assertFalse(isset($this->holidays["not_a_valid_date"]));
    }
    
    public function testOffsetGet()
    {
        $this->assertSame(
            self::NEW_YEARS_DAY_DESCRIPTION,
            $this->holidays[self::NEW_YEARS_DAY_DATE]
        );
        $this->assertSame(
            self::EMPTY_DESCRIPTION,
            $this->holidays[new \DateTime(self::CHRISTMAS_EVE_DATE)]
        );
    }
    
    public function testOffsetUnset()
    {
        $this->assertTrue(isset($this->holidays[self::NEW_YEARS_DAY_DATE]));
        unset($this->holidays[self::NEW_YEARS_DAY_DATE]);
        $this->assertFalse(isset($this->holidays[self::NEW_YEARS_DAY_DATE]));
        unset($this->holidays['not_a_valid_date']);
    }
}
