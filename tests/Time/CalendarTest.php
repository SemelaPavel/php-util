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
use SemelaPavel\Time\Calendar;
use SemelaPavel\Time\Holidays;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class CalendarTest extends TestCase
{
    protected $holidays;
    protected $easterMonday;
    protected $goodFriday;
    protected $saturday;
    protected $sunday;
    
    protected function setUp(): void
    {
        $this->holidays = new Holidays();
        $this->holidays["2020-01-01"] = "New Year";
        $this->holidays[Holidays::goodFriday('2020')] = "Good Friday";
        $this->holidays[Holidays::easterMonday('2020')] = "Easter Monday";
        $this->holidays["2020-05-01"] = "May Day";
        $this->holidays["2020-05-08"] = "Victory in Europe Day";
        $this->holidays["2020-07-05"] = "Saints Cyril nad Methodius";
        $this->holidays["2020-07-06"] = "Jan Hus Day";
        $this->holidays["2020-09-28"] = "St. Wenceslas Day";
        $this->holidays["2020-10-28"] = "Independent Czechoslovak State Day";
        $this->holidays["2020-11-17"] = "Struggle for Freedom and Democracy";
        $this->holidays["2020-12-24"] = "Christmas Eve";
        $this->holidays["2020-12-25"] = "Christmas Day";
        $this->holidays["2020-12-26"] = "St. Stephen's Day";
        
        // Holiday: Easter Monday 2020
        $this->easterMonday = new \DateTime('2020-04-13');
        
        // Holiday: Good Friday 2020
        $this->goodFriday = new \DateTime('2020-04-10');
        
        // Regular Saturday
        $this->saturday = new \DateTime('2020-01-04');
        
        // Regular Sunday before holiday: St. Wenceslas Day
        $this->sunday = new \DateTime('2020-09-27');
        
        // Holiday: Easter Monday 2020
        $this->easterMondayImmutable = new \DateTimeImmutable('2020-04-13');
        
        // Holiday: Good Friday 2020
        $this->goodFridayImmutable = new \DateTimeImmutable('2020-04-10');
        
        // Regular Saturday
        $this->saturdayImmutable = new \DateTimeImmutable('2020-01-04');
        
        // Regular Sunday before holiday: St. Wenceslas Day
        $this->sundayImmutable = new \DateTimeImmutable('2020-09-27');
    }
    
    public function testIsDayOff()
    {
        // Must be a regular days, because of holidays param is missing
        $this->assertFalse(Calendar::isDayOff($this->easterMonday));
        $this->assertFalse(Calendar::isDayOff($this->goodFriday));
        
        $this->assertTrue(Calendar::isDayOff($this->goodFriday, $this->holidays));
        $this->assertTrue(Calendar::isDayOff($this->easterMonday, $this->holidays));
        $this->assertTrue(Calendar::isDayOff($this->saturdayImmutable));
        $this->assertTrue(Calendar::isDayOff($this->sundayImmutable));
    }
    
    public function testNextWorkday()
    {
        // Must be a regular Monday, because of holidays param is missing
        $this->assertEquals(
            $this->easterMonday, 
            Calendar::nextWorkday($this->goodFriday)
        );
        
        // Must be plus one day after Easter Monday
        $this->assertEquals(
            new \DateTime('2020-04-14'), 
            Calendar::nextWorkday($this->goodFridayImmutable, $this->holidays)
        );
        
        // Test if method does not change input value
        $this->assertEquals(new \DateTime('2020-04-10'), $this->goodFriday);
     }
     
     public function testPrevWorkday()
     {
         $this->assertEquals(
            new \DateTime('2020-09-25'), 
            Calendar::prevWorkday($this->sundayImmutable)
        );
         
        // Test if method does not change input value
        $this->assertEquals(new \DateTime('2020-09-27'), $this->sunday);
         
        // Must be a day before Good Friday
        $this->assertEquals(
            new \DateTime('2020-04-09'), 
            Calendar::prevWorkday(new \DateTime('2020-04-14'), $this->holidays)
        );
     }
     
     public function testLastDayOfMonth()
     {
        $this->assertEquals(
            new \DateTime('2020-01-31'), 
            Calendar::lastDayOfMonth($this->saturday)
        );
        
        $this->assertEquals(
            new \DateTime('2020-01-31'), 
            Calendar::lastDayOfMonth($this->saturdayImmutable)
        );
        
        // Test if method does not change input value
        $this->assertEquals(new \DateTime('2020-01-04'), $this->saturday);
     }
     
     public function testLastDayOfPrevMonth()
     {
        $this->assertEquals(
            new \DateTime('2019-12-31'), 
            Calendar::lastDayOfPrevMonth($this->saturday)
        );
        
        $this->assertEquals(
            new \DateTime('2019-12-31'), 
            Calendar::lastDayOfPrevMonth($this->saturdayImmutable)
        );
        
        // Test if method does not change input value
        $this->assertEquals(new \DateTime('2020-01-04'), $this->saturday);
     }
     
     public function testCurrentYear()
     {
         $this->assertEquals(
            (new \DateTime())->format('Y'), 
            Calendar::currentYear()
        );
     }
}
