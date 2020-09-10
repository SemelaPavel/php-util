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

use SemelaPavel\Time\DateTimeParseException;

/**
 * LocalDateTime is object that represents a datetime as year-month-day
 * and hour:minute:second. Time is represented to microsecond precision.
 * This class does not represent a time-zone. Instead, it is a description 
 * of the date, as used for birthdays, combined with the local time 
 * as seen on a wall clock.
 *  
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * @version 2020-07-06
 */
class LocalDateTime extends \DateTime
{
    const CSN_LOCAL_DATE = 'd.m.Y';
    const ISO_LOCAL_DATE = 'Y-m-d';
    const ISO_LOCAL_TIME = 'H:i:s';
    
    const SQL_DATE = 'Y-m-d';
    const SQL_TIME = 'H:i:s';
    const SQL_DATETIME = 'Y-m-d H:i:s';
    
    /**
     * Returns a new LocalDateTime instance.
     * Use timezone parameter with combination of "now" as time parameter 
     * to obtain the current time in specified timezone.
     * 
     * @param string $time The time string to parse.
     * @param \DateTimeZone $timezone Timezone to set the time.
     * @param bool $micro Microsecond precision. Default: true.
     * @throws DateTimeParseException If the time string cannot be parsed.
     */
    public function __construct(
        $time = "now", 
        \DateTimeZone $timezone = null, 
        $micro = true
    ) {
        try {
            parent::__construct(self::normalize($time), $timezone);
            if (!$micro) {
                $this->setTime(
                    $this->getHour(), 
                    $this->getMinute(), 
                    $this->getSecond()
                );
            } 
        } catch (\Exception $e) {
            throw new DateTimeParseException($e);
        }
    }

    /**
     * Obtains an instance of LocalDateTime from an instance
     * implementing \DateTimeInterface (\DateTime or \DateTimeImmutable).
     * 
     * @param \DateTimeInterface $dateTime DateTime object to convert.
     * @param bool $micro Microsecond precision. Default: true.
     * @return LocalDateTime New LocalDateTime instance.
     */
    public static function from(\DateTimeInterface $dateTime, $micro = true)
    {
        return new LocalDateTime(
            $dateTime->format('Y-m-d H:i:s.u'), 
            $dateTime->getTimezone(), 
            $micro
        );
    }

    /**
     * Obtains an instance of LocalDateTime using seconds from the Unix
     * timestamp in seconds from the epoch of 1970-01-01T00:00:00Z.
     *
     * @param int $epochSeconds Seconds from the epoch of 1970-01-01T00:00:00Z.
     * @return LocalDateTime New LocalDateTime instance.
     * @throws \InvalidArgumentException If given number is not a valid timestamp.
     */
    public static function ofUnixTimestamp($epochSeconds)
    {
        try {
            return self::parse(strval($epochSeconds), 'U');
            
        } catch (DateTimeParseException $e) {
            throw new \InvalidArgumentException(
                "Given number is not a valid unix timestamp."
            );
        }
    }

    /**
     * Obtains an instance of LocalDateTime from a text string optionally
     * using a specific datetime format.
     *  
     * @param string $text The text to parse, not null.
     * @param string $format The specific datetime format to use or null.
     * @return \LocalDateTime The parsed local datetime.
     * @throws DateTimeParseException If the text cannot be parsed.
     */
    public static function parse($text, $format = null)
    {
        try {
            if ($format == null) {                
                return self::parseText($text);
                
            } else {
                return self::parseFormat($text, $format);
            }
        } catch (DateTimeParseException $e) {
            throw $e;
        }
    }
    
    /**
     * Compares this datetime to another datetime.
     * The comparsion is based by default on the datetime in local datetime format
     * with microsecond precision. The time zone is not taken into account.
     * 
     * @param \DateTimeInterface $dateTime DateTime object to compare with.
     * @param string $format The specific datetime format to use for comparison. 
     * @return int Negative if less, zero if same, positive if greater.
     */
    public function compareTo(\DateTimeInterface $dateTime, $format = 'YmdHisu')
    {
        $thisDateTime = $this->format($format);
        $dateTimeToCompare = $dateTime->format($format);
        
        if ($thisDateTime < $dateTimeToCompare) {
            return -1;
            
        } elseif ($thisDateTime == $dateTimeToCompare) {
            return 0;
            
        } else {
            return 1;
        }
    }


    /**
     * Compares this datetime to another datetime only by date.
     * 
     * @param \DateTimeInterface $dateTime DateTime object to compare with.
     * @return int Negative if less, zero if same, positive if greater.
     */
    public function compareDateTo(\DateTimeInterface $dateTime)
    {
        return $this->compareTo($dateTime, 'Ymd');
    }
        
    
    /**
     * Checks if this datetime is equal to another datetime.
     * The comparsion is based on the datetime in local datetime format
     * with microsecond precision. No other properties are compared.
     * 
     * @param \DateTimeInterface $dateTime DateTime object to compare with.
     * @return bool True if this is equal to the other datetime object.
     */
    public function equals(\DateTimeInterface $dateTime)
    {
        return $this->format('YmdHisu') == $dateTime->format('YmdHisu');
    }

    /**
     * Returns the primitive int value for the year.
     * 
     * @return int The year.
     */
    public function getYear()
    {
        return (int) $this->format('Y');
    }
    
    /**
     * Returns the month as an int from 1 to 12.
     * 
     * @return int The month-of-year, from 1 to 12.
     */
    public function getMonthValue()
    {
        return (int) $this->format('n');
    }
    
    /**
     * Returns the primitive int value for the day-of-month.
     * 
     * @return int The day-of-month, from 1 to 31.
     */
    public function getDayOfMonth()
    {
        return (int) $this->format('j');
    }
    
    /**
     * Returns the hour-of-day as an int value from 0 to 23.
     * 
     * @return int The hour-of-day, from 0 to 23.
     */
    public function getHour()
    {
        return (int) $this->format('G');
    }
    
    /**
     * Returns the minute-of-hour as an int value from 0 to 59.
     * 
     * @return int The minute-of-hour, from 0 to 59.
     */
    public function getMinute()
    {
        return (int) $this->format('i');
    }
    
    /**
     * Returns the second-of-minute as an int value from 0 to 59.
     * 
     * @return int The second-of-minute, from 0 to 59.
     */
    public function getSecond()
    {
        return (int) $this->format('s');
    }
    
    /**
     * Returns the microsecond-of-second as an int value from 0 to 999999.
     * 
     * @return int The microsecond-of-second, from 0 to 999999.
     */
    public function getMicro()
    {
        return (int) $this->format('u');
    }
    
    /**
     * Returns a copy of this LocalDateTime with the microsecond altered.
     * This instance stay unaffected by this method call.
     * 
     * @param int $microsecond The microsecond-of-second, from 0 to 999999.
     * @return LocalDateTime New datetime with the requested microsecond.
     */
    public function withMicro($microsecond)
    {
        $dateTime = clone $this;
        
        $dateTime->setTime(
            $this->getHour(), 
            $this->getMinute(), 
            $this->getSecond(),
            $microsecond
        );
        
        return $dateTime;
    }
    
    /**
     * Outputs this datetime as a String in local datetime format such
     * as 2020-12-03 10:15:30 or 2020-12-03 10:15:30.207685 if microseconds
     * are set.
     * 
     * @return string A string representation of this datetime.
     */
    public function __toString()
    {
        if ($this->getMicro() == 0) {
            return $this->format('Y-m-d H:i:s');
            
        } else {
            return $this->format('Y-m-d H:i:s.u');
        }
    }
    
    /**
     * Normalizes date-time string to use for DateTime methods.
     * Removes extra spaces and spaces between month and day separators.
     * 
     * @param string $text Date-time string.
     * @return string Normalized date-time string.
     */
    protected static function normalize($text)
    {
        $patterns = array(
            '/([\-\.\:\/\+])\s+/',
            '/([0-9\s]+[T])\s+/',
            '/\s+([\-\.\:\/\+])/',
            '/\s+([T][0-9\s]+)/',
            '/\s{2,}/'
        );
        $replacements = array('\1', '\1', '\1', '\1', ' ');
        $dateStr = trim(preg_replace($patterns, $replacements, $text));
        
        return $dateStr;
    }
    
    /**
     * Obtains an instance of LocalDateTime from a text string.
     * The string must represent a valid datetime.
     * 
     * @param string $text The text to parse, not null.
     * @return LocalDateTime The parsed local datetime.
     * @throws DateTimeParseException If the text cannot be parsed.
     */
    protected static function parseText($text)
    {
        try {
            return new LocalDateTime(is_numeric($text) ? '@' . $text : $text);
            
        } catch (\Exception $e) {
            throw new DateTimeParseException(
                "Given text cannot be parsed as a date."
            );
        }
    }
    
    /**
     * Obtains an instance of LocalDateTime from a text string using 
     * a specific datetime format.
     * The text is parsed using the datetime format, returning a datetime.
     * 
     * @param string $text The text to parse, not null.
     * @param string $format The specific datetime format to use.
     * @return LocalDateTime The parsed local datetime.
     * @throws DateTimeParseException If the text cannot be parsed.
     */
    protected static function parseFormat($text, $format)
    {
        $dateTime = parent::createFromFormat($format, $text);
        
        if ($dateTime) {
            return self::from($dateTime);
            
        } else {
            throw new DateTimeParseException(
                "Given text cannot be parsed to the datetime format."
            );
        }
    }
}
