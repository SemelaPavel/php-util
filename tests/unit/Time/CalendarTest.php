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
use \SemelaPavel\Time\Calendar;
use \SemelaPavel\Time\Holidays;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Time\Calendar
 * @uses \SemelaPavel\Time\Holidays
 */
final class CalendarTest extends TestCase {

    const GOOD_FRIDAY = '2020-04-10';
    const EASTER_MONDAY = '2020-04-13';
    const SATURDAY = '2020-01-04';
    const SUNDAY = '2020-09-27';

    protected \DateTime $easterMonday;
    protected \DateTime $goodFriday;
    protected \DateTime $saturday;
    protected \DateTime $sunday;
    protected \DateTimeImmutable $goodFridayImmutable;
    protected \DateTimeImmutable $saturdayImmutable;
    protected \DateTimeImmutable $sundayImmutable;

    protected function setUp(): void
    {
        $this->goodFriday = new \DateTime(self::GOOD_FRIDAY);
        $this->easterMonday = new \DateTime(self::EASTER_MONDAY);

        $this->goodFridayImmutable = new \DateTimeImmutable(self::EASTER_MONDAY);

        // Regular Saturday
        $this->saturday = new \DateTime(self::SATURDAY);
        $this->saturdayImmutable = new \DateTimeImmutable(self::SATURDAY);

        // Regular Sunday before holiday: St. Wenceslas Day
        $this->sunday = new \DateTime(self::SUNDAY);
        $this->sundayImmutable = new \DateTimeImmutable(self::SUNDAY);
    }
    
    public function holidaysProvider(): array
    {
        $holidays = new Holidays();
        $holidays["2020-01-01"] = "New Year";
        $holidays[self::GOOD_FRIDAY] = "Good Friday";
        $holidays[self::EASTER_MONDAY] = "Easter Monday";
        $holidays["2020-05-01"] = "May Day";
        $holidays["2020-05-08"] = "Victory in Europe Day";
        $holidays["2020-07-05"] = "Saints Cyril nad Methodius";
        $holidays["2020-07-06"] = "Jan Hus Day";
        $holidays["2020-09-28"] = "St. Wenceslas Day";
        $holidays["2020-10-28"] = "Independent Czechoslovak State Day";
        $holidays["2020-11-17"] = "Struggle for Freedom and Democracy";
        $holidays["2020-12-24"] = "Christmas Eve";
        $holidays["2020-12-25"] = "Christmas Day";
        $holidays["2020-12-26"] = "St. Stephen's Day";
        
        return [
            'Holidays' => [$holidays]
        ];
    }

    /**
     * @dataProvider holidaysProvider
     */
    public function testIsDayOff(Holidays $holidays): void
    {
        // Must be a regular days, because of holidays param is missing
        $this->assertFalse(Calendar::isDayOff($this->easterMonday));
        $this->assertFalse(Calendar::isDayOff($this->goodFriday));

        $this->assertTrue(Calendar::isDayOff($this->goodFriday, $holidays));
        $this->assertTrue(Calendar::isDayOff($this->easterMonday, $holidays));
        $this->assertTrue(Calendar::isDayOff($this->saturdayImmutable));
        $this->assertTrue(Calendar::isDayOff($this->sundayImmutable));
    }

    /**
     * @dataProvider holidaysProvider
     */
    public function testNextWorkday(Holidays $holidays):void
    {
        // Must be plus one day after Easter Monday
        $nextWorkDayImmutable = Calendar::nextWorkday($this->goodFridayImmutable, $holidays);
        $this->assertEquals(new \DateTime('2020-04-14'), $nextWorkDayImmutable);
        $this->assertTrue($nextWorkDayImmutable instanceof \DateTimeImmutable);

        // Must be a regular Monday, because of holidays param is missing
        $nextWorkDay = Calendar::nextWorkday($this->goodFriday);
        $this->assertEquals($this->easterMonday, $nextWorkDay);
        $this->assertTrue($nextWorkDay instanceof \DateTime);

        // Test if method does not change input value
        $this->assertEquals(new \DateTime(self::GOOD_FRIDAY), $this->goodFriday);
    }

    /**
     * @dataProvider holidaysProvider
     */
    public function testPrevWorkday(Holidays $holidays): void
    {
        // Must be a day before Good Friday
        $prevWorkDay = Calendar::prevWorkday(new \DateTime('2020-04-14'), $holidays);
        $this->assertEquals(new \DateTime('2020-04-09'), $prevWorkDay);
        $this->assertTrue($prevWorkDay instanceof \DateTime);

        $prevWorkDayImmutable = Calendar::prevWorkday($this->sundayImmutable);
        $this->assertEquals(new \DateTime('2020-09-25'), $prevWorkDayImmutable);
        $this->assertTrue($prevWorkDayImmutable instanceof \DateTimeImmutable);

        $this->assertEquals(new \DateTime('2020-09-25'), Calendar::prevWorkday($this->sunday));

        // Test if method does not change input value
        $this->assertEquals(new \DateTime(self::SUNDAY), $this->sunday);
    }

    public function testLastDayOfMonth():void
    {
        $lastDayOfMonthImmutable = Calendar::lastDayOfMonth($this->saturdayImmutable);
        $this->assertEquals(new \DateTime('2020-01-31'), $lastDayOfMonthImmutable);
        $this->assertTrue($lastDayOfMonthImmutable instanceof \DateTimeImmutable);

        $lastDayOfMonth = Calendar::lastDayOfMonth($this->saturday);
        $this->assertEquals(new \DateTime('2020-01-31'), $lastDayOfMonth);
        $this->assertTrue($lastDayOfMonth instanceof \DateTime);

        // Test if method does not change input value
        $this->assertEquals(new \DateTime(self::SATURDAY), $this->saturday);
    }

    public function testLastDayOfPrevMonth():void
    {
        $lastDayOfPrevMonthImmutable = Calendar::lastDayOfPrevMonth($this->saturdayImmutable);
        $this->assertEquals(new \DateTime('2019-12-31'), $lastDayOfPrevMonthImmutable);
        $this->assertTrue($lastDayOfPrevMonthImmutable instanceof \DateTimeImmutable);

        $lastDayOfPrevMonth = Calendar::lastDayOfPrevMonth($this->saturday);
        $this->assertEquals(new \DateTime('2019-12-31'), $lastDayOfPrevMonth);
        $this->assertTrue($lastDayOfPrevMonth instanceof \DateTime);

        // Test if method does not change input value
        $this->assertEquals(new \DateTime(self::SATURDAY), $this->saturday);
    }

    public function testCurrentYear(): void
    {
        $this->assertEquals((new \DateTime())->format('Y'), Calendar::currentYear());
    }
}
