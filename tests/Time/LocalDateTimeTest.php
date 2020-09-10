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
use SemelaPavel\Time\DateTimeParseException;
use SemelaPavel\Time\LocalDateTime;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * @version 2020-07-06
 */
final class LocalDateTimeTest extends TestCase
{
    const DATETIME_MICRO_STR = '2020-07-06 13:37:00.001337';
    const DATETIME_NORMALIZED = '2020-07-06T13:37:00.001337+01:00';
    const DATETIME_TONORMALIZE = ' 2020-  07 - 06 T 13 :37 : 00 . 001337  + 02: 00 ';
    
    const TIMESTAMP = 1594042620;
    
    const MICRO_FORMAT = 'Y-m-d H:i:s.u';
    const MIN_FORMAT = 'Y-m-d H:i';
    
    protected $timezonePrague;
    protected $dateTimeMicro;
    
    protected function setUp(): void
    {
        $this->timezonePrague = new \DateTimeZone('Europe/Prague');
        
        $this->dateTimeMicro = new \DateTime(
            self::DATETIME_MICRO_STR,
            $this->timezonePrague
        );
    }
    
    public function testConstruct()
    {
        $DateTimeNow = new DateTime();
        $localDateTimeNow = new LocalDateTime();
        $this->assertEquals(
            $DateTimeNow->format(self::MIN_FORMAT), 
            $localDateTimeNow->format(self::MIN_FORMAT)
        );
   
        $DateTimeNowTz = new DateTime('now', $this->timezonePrague);
        $localDateTimeNowTz = new LocalDateTime('now', $this->timezonePrague);
        $this->assertEquals(
            $DateTimeNowTz->format(self::MIN_FORMAT),
            $localDateTimeNowTz->format(self::MIN_FORMAT)
        );

        $dateTime = new \DateTime(self::DATETIME_NORMALIZED);
        $localDateTime = new LocalDateTime(self::DATETIME_TONORMALIZE);
        
        $this->assertEquals(
            $dateTime->format(self::MICRO_FORMAT), 
            $localDateTime->format(self::MICRO_FORMAT)
        );
    }
    
    public function testConstructException()
    {
        $this->expectException(DateTimeParseException::class);
        new LocalDateTime('not a date');
    }
    
    public function testFrom()
    {
        $this->assertEquals(
            $this->dateTimeMicro->format(self::MICRO_FORMAT), 
            (LocalDateTime::from($this->dateTimeMicro))->format(self::MICRO_FORMAT)
        );
        
        $this->assertNotEquals(
            $this->dateTimeMicro->format(self::MICRO_FORMAT), 
            (LocalDateTime::from($this->dateTimeMicro, false))->format(self::MICRO_FORMAT)
        );
    }
    
    public function testOfUnixTimestamp()
    {
        $dateTime = new \DateTime('@' . self::TIMESTAMP);
        $localDateTime = LocalDateTime::ofUnixTimestamp(self::TIMESTAMP);
        
        $this->assertEquals(
            $dateTime->format(self::MICRO_FORMAT),
            $localDateTime->format(self::MICRO_FORMAT)
        );
    }
    
    public function testOfUnixTimestampException()
    {
        $this->expectException(\InvalidArgumentException::class);
        LocalDateTime::ofUnixTimestamp('not a timestamp');
    }
    
    public function testParseText()
    {
        $this->assertEquals( 
            $this->dateTimeMicro->format(self::MICRO_FORMAT),
            (LocalDateTime::parse(self::DATETIME_MICRO_STR))->format(self::MICRO_FORMAT)
        );
        
        $dateTime = new \DateTime('@' . self::TIMESTAMP);
        $localDateTime = LocalDateTime::parse(self::TIMESTAMP);
        
        $this->assertEquals(
            $dateTime->format(self::MICRO_FORMAT),
            $localDateTime->format(self::MICRO_FORMAT)
        );
        
        $dateTime2 = new \DateTime(self::DATETIME_NORMALIZED);
        $localDateTime2 = LocalDateTime::parse(self::DATETIME_TONORMALIZE);
        
        $this->assertEquals(
            $dateTime2->format(self::MICRO_FORMAT),
            $localDateTime2->format(self::MICRO_FORMAT)
        );
    }
    
    public function testParseTextException()
    {
        $this->expectException(DateTimeParseException::class);
        LocalDateTime::parse('not a date');
    }
    
    public function testParseFromFormat()
    {
        $localDateTimeFromFormat = LocalDateTime::parse(
            $this->dateTimeMicro->format(self::MICRO_FORMAT), 
            self::MICRO_FORMAT
        );
        
        $this->assertEquals(
            $this->dateTimeMicro->format(self::MICRO_FORMAT),
            $localDateTimeFromFormat->format(self::MICRO_FORMAT)
        );
    }
    
    public function testParseFromFormatException()
    {
        $this->expectException(DateTimeParseException::class);
        LocalDateTime::parse('not a date', self::MICRO_FORMAT);
    }
    
    public function testCompareTo()
    {
        $localDateTime = new LocalDateTime(self::DATETIME_MICRO_STR);
        $this->assertEquals(0, $localDateTime->compareTo($this->dateTimeMicro));
        
        $localDateTime2 = new LocalDateTime('2020-01-01 13:37');
        $dateTime2 = new \DateTime('2020-01-01 13:36');
        $this->assertEquals(1, $localDateTime2->compareTo($dateTime2));
        
        $localDateTime3 = new LocalDateTime('2019-12-31 23:59:59');
        $dateTime3 = new \DateTime('2020-01-01');
        $this->assertEquals(-1, $localDateTime3->compareTo($dateTime3));
    }
    
    public function testCompareDateTo()
    {
        $localDateTime1 = new LocalDateTime('2020-01-01 13:37');
        $dateTime1 = new \DateTime('2020-01-01 13:36');
        $this->assertEquals(0, $localDateTime1->compareDateTo($dateTime1));
        
        $localDateTime2 = new LocalDateTime('2020-01-02 13:37');
        $dateTime2 = new \DateTime('2020-01-01 13:37');
        $this->assertEquals(1, $localDateTime2->compareDateTo($dateTime2));
        
        $localDateTime3 = new LocalDateTime('2019-12-31 13:37');
        $dateTime3 = new \DateTime('2020-01-01 13:37');
        $this->assertEquals(-1, $localDateTime3->compareDateTo($dateTime3));
    }
    
    public function testEquals()
    {
        $localDateTime = new LocalDateTime(self::DATETIME_MICRO_STR);
        $this->assertTrue($localDateTime->equals($this->dateTimeMicro));
        
        $localDateTime2 = new LocalDateTime(self::DATETIME_MICRO_STR, null, false);
        $this->assertFalse($localDateTime2->equals($this->dateTimeMicro));
    }
    
    public function testGet()
    {
        $localDateTime = new LocalDateTime('2020-07-06 13:37:11.123456');
        $this->assertSame(2020, $localDateTime->getYear());
        $this->assertSame(7, $localDateTime->getMonthValue());
        $this->assertSame(6, $localDateTime->getDayOfMonth());
        $this->assertSame(13, $localDateTime->getHour());
        $this->assertSame(37, $localDateTime->getMinute());
        $this->assertSame(11, $localDateTime->getSecond());
        $this->assertSame(123456, $localDateTime->getMicro());
    }
    
    public function testWithMicro()
    {
        $localDateTime = new LocalDateTime(self::DATETIME_MICRO_STR);
        $newLocalDateTime = $localDateTime->withMicro(0);
        
        $this->assertNotEquals(
            $localDateTime->format(self::MICRO_FORMAT), 
            $newLocalDateTime->format(self::MICRO_FORMAT)
        );
    }
    
    public function testToString()
    {
        $localDateTime = new LocalDateTime('2020-07-06 13:37:11.123456');
        
        ob_start();
        echo $localDateTime;
        $output = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals(
            $localDateTime->format('Y-m-d H:i:s.u'), 
            $output);
        
        $localDateTime2 = new LocalDateTime('2020-07-06 13:37:11');
        
        ob_start();
        echo $localDateTime2;
        $output2 = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals(
            $localDateTime2->format('Y-m-d H:i:s'), 
            $output2);
    }
}
