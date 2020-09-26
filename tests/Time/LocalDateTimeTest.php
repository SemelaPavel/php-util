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
 */
final class LocalDateTimeTest extends TestCase
{
    const DATETIME_STR = '2020-07-06 13:37:00.001337';
    const TIMESTAMP = 1594042620;
    
    protected $dateTime;
    protected $tzDefault;
    protected $tzLocal;
    
    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
                
        $this->dateTime = new \DateTime(self::DATETIME_STR);
        $this->tzDefault = new \DateTimeZone('UTC');
        $this->tzLocal = new \DateTimeZone('Europe/Prague');
    }
    
    public function testLocalTimeZone()
    {
        $this->assertEquals($this->tzDefault, LocalDateTime::getLocalTimeZone());
        
        LocalDateTime::setLocalTimeZone($this->tzLocal);
        
        $this->assertEquals($this->tzLocal, LocalDateTime::getLocalTimeZone());
        $this->assertEquals($this->tzLocal, (new \DateTime())->getTimezone());
    }
    
    public function testNow()
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
    }
    
    public function testToday()
    {
        $this->assertEquals(
            new \DateTime((new \DateTime())->format('Y-m-d')),
            LocalDateTime::today()
        );
    }
    
    public function testOfUnixTimestamp()
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
    
    public function testOfUnixTimestampException()
    {
        $this->expectException(\InvalidArgumentException::class);
        LocalDateTime::ofUnixTimestamp('not a timestamp');
    }
    
    public function testNormalize()
    {
        $this->assertSame(
            '2020-07-06T13:37:00.001337+01:00',
            LocalDateTime::normalize(' 2020-  07 - 06 T 13 :37 : 00 . 001337  + 01: 00 ')
        );
    }
    
    public function testParseText()
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
            LocalDateTime::parse(self::TIMESTAMP)
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
            $this->dateTime->format('Y-m-d H:i:s.u'), 
            'Y-m-d H:i:s.u'
        );
        
        $this->assertEquals(
            $this->dateTime,
            $localDateTimeFromFormat
        );
    }
    
    public function testParseFromFormatException()
    {
        $this->expectException(DateTimeParseException::class);
        LocalDateTime::parse('not a date', 'Y-m-d H:i:s.u');
    }
}
