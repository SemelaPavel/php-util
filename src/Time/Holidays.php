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

/**
 * ArrayAccess class which handles holidays and provides
 * some extra functionality e.g. calculation of some holiday dates as Easter.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class Holidays implements \ArrayAccess
{
    /** 
     * @var array Associative array of YYYY-MM-DD and holiday description. 
     */
    protected $holidays = [];

    /**
     * Calculates Easter date for given year.
     * 
     * @param string $year Year in YYYY format.
     * @return \DateTime New \DateTime object with Easter date.
     * @throws \InvalidArgumentException If given year is not in right format.
     */
    public static function easter($year)
    {
        $base = \DateTime::createFromFormat("Y-m-d|", "{$year}-03-21");
        
        if ($base) {
            $days = easter_days($year);
            
            return $base->add(new \DateInterval("P{$days}D"));
            
        } else {
            throw new \InvalidArgumentException('String is not in YYYY format!');
        } 
    }

    /**
     * Calculates date of Good Friday day for given year.
     * 
     * @param string $year Year in YYYY format.
     * @return \DateTime New \DateTime object with Good Friday date.
     * @throws \InvalidArgumentException If given year is not in right format.
     */
    public static function goodFriday($year)
    {
        try {
            
            return self::easter($year)->sub(new \DateInterval("P2D"));
            
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Calculates date of Easter Monday day for given year.
     * 
     * @param string $year Year in YYYY format.
     * @return \DateTime New \DateTime object with Easter Monday date.
     * @throws \InvalidArgumentException If given year is not in right format.
     */
    public static function easterMonday($year)
    {
        try {
            
            return self::easter($year)->add(new \DateInterval("P1D"));
            
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Checks if the given key already exists in holidays array or not.
     * 
     * @param \DateTimeInterface|string $date DateTime... or string as YYYY-MM-DD.
     * @return bool TRUE if the key exists in holidays array or FALSE if not.
     */
    public function offsetExists($date)
    {
        try {
            
            return array_key_exists($this->parseOffset($date), $this->holidays);
            
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Finds and returns holiday name or description for the given date
     * if such a date exists in holidays array.
     * 
     * @param \DateTimeInterface|string $date DateTime... or string as YYYY-MM-DD.
     * @return string Holiday name or description for the given date.
     * @throws \InvalidArgumentException If given date is not in right format.
     */
    public function offsetGet($date)
    {
        try {
            
            return $this->holidays[$this->parseOffset($date)];
            
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Adds new holiday date and its name or description in holidays array
     * or update existing date with new holiday name or description.
     * 
     * @param \DateTimeInterface|string $date DateTime... or string as YYYY-MM-DD.
     * @param string $name Holiday name or description fot given date.
     * @throws \InvalidArgumentException If given date is not in right format.
     */
    public function offsetSet($date, $name)
    {
        try {
            
            $this->holidays[$this->parseOffset($date)] = $name;
            
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Removes given date from holidays array.
     * 
     * @param \DateTimeInterface|string $date DateTime... or string as YYYY-MM-DD.
     */
    public function offsetUnset($date)
    {
        try {
            
            unset($this->holidays[$this->parseOffset($date)]);
            
        } catch (\InvalidArgumentException $e) {
            // Nothing to do :-)
        }
    }

    /**
     * Returns internal array of ArrayAccess Holidays object.
     * 
     * @return array Internal array of ArrayAccess object.
     */
    public function toArray()
    {
        return $this->holidays;
    }

    /**
     * Parses date from given string or \DateTime object and 
     * returns this date formated as string in YYYY-MM-DD format.
     * 
     * @param \DateTimeInterface|string $date DateTime... or string as YYYY-MM-DD.
     * @return string Formated date string as YYYY-MM-DD.
     * @throws \InvalidArgumentException If given date is not in right format.
     */
    protected function parseOffset($date)
    {
        if (!($date instanceof \DateTimeInterface)) {
            $date = \DateTime::createFromFormat("Y-m-d|", strval($date));
        }
        
        if ($date) {
            return $date->format("Y-m-d");
        } else {
            throw new \InvalidArgumentException('String is not in YYYY-MM-DD format!');
        }
    }
}
