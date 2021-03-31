<?php declare (strict_types = 1);
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
 * 
 * @covers \SemelaPavel\Time\Holidays
 */
final class HolidaysTest extends TestCase
{
    const NEW_YEARS_DAY_DATE = "2020-01-01";
    const NEW_YEARS_DAY_DESCRIPTION = "New Years's Day";
    const CHRISTMAS_EVE_DATE = "2020-12-24";
    const EMPTY_DESCRIPTION = "";
    const NOT_IN_ARRAY_KEY = "2019-01-01";
        
    /**
     * @doesNotPerformAssertions
     */
    public function testOffsetSet()
    {
        $holidays = new Holidays();
                
        $holidays[
            self::NEW_YEARS_DAY_DATE
        ] = self::NEW_YEARS_DAY_DESCRIPTION;
        
        $holidays[
            new \DateTime(self::CHRISTMAS_EVE_DATE)
        ] = self::EMPTY_DESCRIPTION;
        
        return $holidays;
    }
    
    public function offsetsProvider()
    {
        return [
            'New Years date' => [self::NEW_YEARS_DAY_DATE, true],
            'Christmas Eve date' => [self::CHRISTMAS_EVE_DATE, true],
            'New Years DateTime' => [new \DateTime(self::NEW_YEARS_DAY_DATE), true],
            'Christmas Eve DateTime' => [new \DateTime(self::CHRISTMAS_EVE_DATE), true],
            'Not in array as valid date' => [self::NOT_IN_ARRAY_KEY, false],
            'Not in array as invalid date' => ["not_a_valid_date", false]
        ];
    }
    
    public function falseDateTimeProvider()
    {
        return [
            'Date-time as null' => [null],
            'Date-time as empty string' => [' '],
            'DateTime in wrong format' => ['2021.01.01'],
            'Date-time as zero' => [0],
            'Date-time as year only' => ['2021']
        ];
    }
    
    public function testEaster()
    {
        $easter = new \DateTimeImmutable("2020-04-12");
        $this->assertEquals($easter, Holidays::easter(2020));
        $this->assertEquals($easter, Holidays::easter((int) '2020'));
        $this->assertNotEquals($easter, Holidays::easter(2019));
    }

    public function testGoodFriday()
    {
        $goodFriday = new \DateTimeImmutable("2020-04-10");
        $this->assertEquals($goodFriday, Holidays::goodFriday(2020));
        $this->assertEquals($goodFriday, Holidays::goodFriday((int) "2020"));
        $this->assertNotEquals($goodFriday, Holidays::goodFriday(2019));
    }
    
    public function testEasterMonday()
    {
        $easterMonday = new \DateTimeImmutable("2020-04-13");
        $this->assertEquals($easterMonday, Holidays::easterMonday(2020));
        $this->assertEquals($easterMonday, Holidays::easterMonday((int) 2020));
        $this->assertNotEquals($easterMonday, Holidays::easterMonday(2019));
    }
    
    /**
     * @dataProvider offsetsProvider
     * @depends testOffsetSet
     */
    public function testOffsetExists($dateTime, $offSetExists, $holidays)
    {
        $this->assertSame($offSetExists, isset($holidays[$dateTime]));
    }
    
    /**
     * @depends testOffsetSet
     */
    public function testOffsetGet($holidays)
    {
        $this->assertSame(
            self::NEW_YEARS_DAY_DESCRIPTION,
            $holidays[self::NEW_YEARS_DAY_DATE]
        );
        $this->assertSame(
            self::EMPTY_DESCRIPTION,
            $holidays[new \DateTime(self::CHRISTMAS_EVE_DATE)]
        );
    }
    
    /**
     * @dataProvider falseDateTimeProvider
     * @depends testOffsetSet
     */
    public function testOffsetGetInvalidArgumentException($dateTime, $holidays)
    {
        $this->expectException(\InvalidArgumentException::class);
        $holidays[$dateTime];
    }
    
    /**
     * @depends testOffsetSet
     */
    public function testOffsetSetAndChangeDescription($holidays)
    {
        $holidays[self::NEW_YEARS_DAY_DATE] = 'no description';
        $this->assertSame('no description', $holidays[self::NEW_YEARS_DAY_DATE]);
    }
    
    /**
     * @dataProvider falseDateTimeProvider
     * @depends testOffsetSet
     */
    public function testOffsetSetInvalidArgumentException($dateTime, $holidays)
    {
        $this->expectException(\InvalidArgumentException::class);
        $holidays[$dateTime] = '';
    }
    
    /**
     * @depends testOffsetSet
     */
    public function testOffsetUnset($holidays)
    {
        $this->assertTrue(isset($holidays[self::NEW_YEARS_DAY_DATE]));
        unset($holidays[self::NEW_YEARS_DAY_DATE]);
        $this->assertFalse(isset($holidays[self::NEW_YEARS_DAY_DATE]));
        unset($holidays['not_a_valid_date']);
    }
}
