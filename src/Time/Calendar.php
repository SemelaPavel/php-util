<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Time;

use SemelaPavel\Time\Holidays;

/**
 * Calendar class that helps with some dates calculations.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class Calendar
{
    /**
     * Evaluate if the given date is a day off (weekend or holiday, 
     * if holidays are set) or not.
     * 
     * @param \DateTimeInterface $date Date to be evaluated.
     * @param Holidays $holidays ArrayAccess object that contains holidays.
     * @return bool True if the date is a day off or false if it is a workday.
     */
    public static function isDayOff(\DateTimeInterface $date, Holidays $holidays = null)
    {
        return isset($holidays[$date]) || $date->format('N') > 5;
    }
    
    /**
     * Finds next workday closest to the given date.
     * Given date is not modified.
     * 
     * @param \DateTimeInterface $date Starting date to search next workday.
     * @param Holidays $holidays ArrayAccess object that contains holidays.
     * @return \DateTime|\DateTimeImmutable Next workday.
     */
    public static function nextWorkday(\DateTimeInterface $date, Holidays $holidays = null)
    {
        $newDate = clone $date;
        do {
           $newDate = $newDate->modify("next weekday"); 
        } while (static::isDayOff($newDate, $holidays));
        
        return $newDate;
    }
    
    /**
     * Finds previous workday closest to the given date.
     * Given date is not modified.
     * 
     * @param \DateTimeInterface $date Starting date to search next workday.
     * @param Holidays $holidays ArrayAccess object that contains holidays.
     * @return \DateTime|\DateTimeImmutable Previous workday.
     */
    public static function prevWorkday(\DateTimeInterface $date, Holidays $holidays = null)
    {
        $newDate = clone $date;
        do {
           $newDate = $newDate->modify("previous weekday"); 
        } while (static::isDayOff($newDate, $holidays));
        
        return $newDate;
    }
    
    /**
     * Finds the last day of the given month.
     * Given date is not modified.
     * 
     * @param \DateTimeInterface $date Starting day to search.
     * @return \DateTime|\DateTimeImmutable Last day of the month.
     */
    public static function lastDayOfMonth(\DateTimeInterface $date)
    {
        $newDate = clone $date;
        
        return $newDate->modify("last day of this month");
    }
    
    /**
     * Finds previous day of the given month.
     * Given date is not modified.
     * 
     * @param \DateTimeInterface $date Starting day to search.
     * @return \DateTime|\DateTimeImmutable Last day of previous month.
     */
    public static function lastDayOfPrevMonth(\DateTimeInterface $date)
    {
        $newDate = clone $date;
        
        return $newDate->modify("last day of previous month");
    }
    
    /**
     * Returns string representation of current year in YYYY format.
     * 
     * @return string String representation of current year in YYYY format.
     */
    public static function currentYear()
    {
        return (new \DateTime())->format('Y');
    }
}
