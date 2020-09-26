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
 * DateTime factory.
 * All functions in this class use default time zone.
 *  
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class LocalDateTime
{
    const CSN_DATE = 'd.m.Y';
    const ISO_DATE = 'Y-m-d';
    const ISO_TIME = 'H:i:s';
    
    const SQL_DATE = 'Y-m-d';
    const SQL_TIME = 'H:i:s';
    const SQL_DATETIME = 'Y-m-d H:i:s';
    
    /**
     * Sets default time zone.
     * 
     * @param \DateTimeZone $timezone Time zone.
     */
    public static function setLocalTimeZone(\DateTimeZone $timezone)
    {
        date_default_timezone_set($timezone->getName());
    }
    
    /**
     * Returns default time zone.
     * 
     * @return \DateTimeZone Default TimeZone.
     */
    public static function getLocalTimeZone()
    {
        return new \DateTimeZone(date_default_timezone_get());
    }
    
    /**
     * Obtains an instance of \DateTime set to current date-time. If format
     * is used, then obtained date-time is modified acording to the format.
     * 
     * @param string $format The specific date-time format to use or null.
     * @return \DateTime New \DateTime instance.
     */
    public static function now($format = null)
    {
        if ($format != null) {
            $dateTimeStr = (new \DateTime())->format($format);
            
            if ($dateTimeStr) {
                return new \DateTime($dateTimeStr);

            } else {
                throw new \InvalidArgumentException(
                    "Given format is not a valid DateTime format."
                );
            }
        } else {
            return new \DateTime();
        }
    }
    
    /**
     * Obtains an instance of \DateTime set to current date with time set to 0.
     * 
     * @return \DateTime New \DateTime instance.
     */
    public static function today()
    {
        return self::now(self::ISO_DATE);
    }
        
    /**
     * Obtains an instance of \DateTime using seconds from the Unix
     * timestamp in seconds from the epoch of 1970-01-01T00:00:00Z.
     * The resulting time is adjusted according to the default local time zone. 
     *
     * @param int|string $epochSeconds Seconds from the epoch of 1970-01-01T00:00:00Z.
     * @return \DateTime New \DateTime instance.
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
     * Normalizes date-time string to use for \DateTime methods.
     * Removes extra spaces and spaces between month and day separators.
     * 
     * @param string $text Date-time string.
     * @return string Normalized date-time string.
     */
    public static function normalize($text)
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
     * Obtains an instance of \DateTime from a text string optionally
     * using a specific date-time format. Timezone is set to local time zone.
     *  
     * @param string $text The text to parse, not null.
     * @param string $format The specific date-time format to use or null.
     * @return \DateTime The parsed date-time.
     * @throws DateTimeParseException If the text cannot be parsed.
     */
    public static function parse($text, $format = null)
    {
        try {
            if ($format == null) {                
                $dateTime = self::parseText($text);
            } else {
                $dateTime = self::parseFormat($text, $format);
            }
            
            return $dateTime->setTimezone(self::getLocalTimeZone());
            
        } catch (DateTimeParseException $e) {
            throw $e;
        }
    }
    
    /**
     * Obtains an instance of \DateTime from a text string.
     * The string must represent a valid date-time.
     * 
     * @param string $text The text to parse, not null.
     * @return \DateTime The parsed date-time.
     * @throws DateTimeParseException If the text cannot be parsed.
     */
    protected static function parseText($text)
    {
        try {
            return new \DateTime(is_numeric($text) ? '@' . $text : $text);
            
        } catch (\Exception $e) {
            throw new DateTimeParseException(
                "Given text cannot be parsed as a date."
            );
        }
    }
    
    /**
     * Obtains an instance of \DateTime from a text string using 
     * a specific date-time format.
     * The text is parsed using the date-time format, returning a date-time.
     * 
     * @param string $text The text to parse, not null.
     * @param string $format The specific date-time format to use.
     * @return \DateTime The parsed date-time.
     * @throws DateTimeParseException If the text cannot be parsed.
     */
    protected static function parseFormat($text, $format)
    {
        $dateTime = \DateTime::createFromFormat($format, $text);
        
        if ($dateTime) {
            return $dateTime;
            
        } else {
            throw new DateTimeParseException(
                "Given text cannot be parsed to the datetime format."
            );
        }
    }
}
