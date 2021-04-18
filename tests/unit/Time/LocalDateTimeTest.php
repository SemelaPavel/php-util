<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\UnitTests\Time;

use \PHPUnit\Framework\TestCase;
use \SemelaPavel\Time\Exception\DateTimeParseException;
use \SemelaPavel\Time\LocalDateTime;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Time\LocalDateTime
 * @uses \SemelaPavel\Time\Exception\DateTimeParseException
 */
final class LocalDateTimeTest extends TestCase
{
    const DATETIME_STR = '2020-07-06 13:37:00.001337';
    const TIMESTAMP = 1594042620;
    
    protected \DateTime $dateTime;
    protected \DateTimeZone $tzDefault;
    protected \DateTimeZone $tzLocal;
    
    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
                
        $this->dateTime = new \DateTime(self::DATETIME_STR);
        $this->tzDefault = new \DateTimeZone('UTC');
        $this->tzLocal = new \DateTimeZone('Europe/Prague');
    }
    
    public function testLocalTimeZone(): void
    {
        $this->assertEquals($this->tzDefault, LocalDateTime::getLocalTimeZone());
        
        LocalDateTime::setLocalTimeZone($this->tzLocal);
        
        $this->assertEquals($this->tzLocal, LocalDateTime::getLocalTimeZone());
        $this->assertEquals($this->tzLocal, (new \DateTime())->getTimezone());
    }
    
    public function testNow(): void
    {
        $format = 'Y-m-d H:i';
        
        $this->assertSame(
            (new \DateTime())->format($format), 
            (LocalDateTime::now()->format($format))
        );
        
        $this->assertEquals(
            new \DateTime((new \DateTime())->format($format)),
            LocalDateTime::now($format)
        );
        
        $this->assertSame(
            (new \DateTime())->format($format),
            LocalDateTime::now('')->format($format)
        );
    }
    
    public function testNowInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LocalDateTime::now('not a valid format');
    }
    
    public function testToday(): void
    {
        $this->assertEquals(
            new \DateTime((new \DateTime())->format('Y-m-d')),
            LocalDateTime::today()
        );
    }
    
    public function testOfUnixTimestamp(): void
    {
        $dateTime = new \DateTime('@' . self::TIMESTAMP);
        
        $this->assertEquals(
            $dateTime,
            LocalDateTime::ofUnixTimestamp(self::TIMESTAMP)
        );

        LocalDateTime::setLocalTimeZone($this->tzLocal);
        
        $this->assertEquals(
            $dateTime,
            LocalDateTime::ofUnixTimestamp(self::TIMESTAMP)    
        );
    }
    
    public function testNormalize(): void
    {
        $this->assertSame(
            '2020-07-06T13:37:00.001337+01:00',
            LocalDateTime::normalize(' 2020-  07 - 06 T 13 :37 : 00 . 001337  + 01: 00 ')
        );
        $this->assertSame(
            '2020-07-06 13:37:00.001337',
            LocalDateTime::normalize(' 2020-  07 - 06  13 :37 : 00 . 001337 ')
        );
        $this->assertSame(
            '2020-07-06T13:37:00.001337Z',
            LocalDateTime::normalize(' 2020-  07 - 06 T 13 :37 : 00  . 001337  Z ')
        );
    }
    
    public function testParseText(): void
    {
        $this->assertEquals( 
            $this->dateTime,
            LocalDateTime::parse(self::DATETIME_STR)
        );
        
        LocalDateTime::setLocalTimeZone($this->tzLocal);
        
        $this->assertEquals( 
            new \DateTime(self::DATETIME_STR),
            LocalDateTime::parse(self::DATETIME_STR)
        );
        
        $this->assertEquals(
            new \DateTime('@' . self::TIMESTAMP), 
            LocalDateTime::parse(strval(self::TIMESTAMP))
        );
    }
    
    public function testParseTextException(): void
    {
        $this->expectException(DateTimeParseException::class);
        LocalDateTime::parse('not a date');
    }
    
    public function testParseFromFormat(): void
    {
        $localDateTimeFromFormat = LocalDateTime::parse(
            $this->dateTime->format('Y-m-d H:i:s.u'), 
            'Y-m-d H:i:s.u'
        );
        
        $this->assertEquals(
            $this->dateTime,
            $localDateTimeFromFormat
        );
    }
    
    public function testParseFromFormatException(): void
    {
        $this->expectException(DateTimeParseException::class);
        LocalDateTime::parse('not a date', 'Y-m-d H:i:s.u');
    }
}
