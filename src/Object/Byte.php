<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Object;

use SemelaPavel\Object\Exception\ByteParseException;

/**
 * This class wraps an integer value and represents it as a binary byte.
 * This class also provides several methods for parsing byte from strings,
 * or other binary values like KB, MB, GB and TB and some other useful
 * methods and constants when dealing with a byte.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class Byte
{
    const MAX_VALUE = PHP_INT_MAX;
    const MIN_VALUE = 0;
    
    /** Byte value of KiB/KB in JEDEC standard. */
    const KB = 1024;
    
    /** Byte value of MiB/MB in JEDEC standard. */
    const MB = 1048576;
    
    /** Byte value of GiB/GB in JEDEC standard. */
    const GB = 1073741824;
    
    /** Byte value of TiB/TB in JEDEC standard. */
    const TB = 1099511627776;
    
    protected $value;

    /**
     * Returns new Byte object that represents the specified byte value.
     * 
     * @param int $value The value to be represented by the Byte.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    /**
     * Returns new Byte object that represents the specified byte value 
     * obtained on the basis of the given value and its binary unit.
     * 
     * e.g. Byte::from(1, 'KB') = Byte(1024)
     * e.g. Byte::from(1, 'KiB') = Byte(1024)
     * 
     * @param int|float $value The value to be represented by the Byte.
     * @param string $unit Binary unit in ISO/IEC 80000 or JEDEC standard.
     * 
     * @return Byte New Byte object.
     * 
     * @throws ByteParseException If the given binary unit cannot be recognised.
     */
    public static function from($value, $unit)
    {
        $byteValue = static::byteValueOfUnit($unit);
        
        if ($byteValue) {
            return new static((int) ($value * $byteValue));
            
        } else {
            throw new ByteParseException('The given binary unit cannot be recognised.');
        }
    }
    
    /**
     * Parses the string argument as a numerical value with binary units added.
     * The result is a Byte object that represents the byte value specified
     * by the string. Binary units must be in ISO/IEC 80000 or JEDEC standard.
     * The string may contain white spaces between numerical and unit part. 
     * Numerical value representing an integer without binary units is recognised
     * as a byte.
     *
     * e.g. Byte::parse('1KB') = Byte(1024) 
     * e.g. Byte::parse('1 KB') = Byte(1024)
     * 
     * @param string $byteStr String with numerical value and binary units.
     * 
     * @return Byte New Byte object.
     * 
     * @throws ByteParseException If the given string cannot be parsed as a byte.
     */
    public static function parse($byteStr)
    {
        $int = '^([1-9][0-9]*)$';
        $b = '^(0|[1-9][0-9]*)\s*(B)$';
        $kmgt = '^((?:0|[1-9][0-9]*)(?:[\.\,][0-9]+)?)\s*([KMGT][i]?B)$';
        $matches = [];
        
        preg_match("/(?|{$kmgt}|{$b}|{$int})/", trim($byteStr), $matches);
        
        if (count($matches) == 3) {
            return static::from((float) str_replace(',', '.', $matches[1]), $matches[2]);
            
        } elseif (count($matches) == 2) {
             return new static((int) $matches[1]);
             
        } else {
            throw new ByteParseException('The given string cannot be parsed as a byte.');
        }
    }
    
    /**
     * These shorthand notations may be used in php.ini and obtained by ini_get()
     * function. Note that the numeric value is cast to integer; for instance,
     * 0.5M is interpreted as 0. The available options are K, M, G and are
     * all case-insensitive and represents binary units.
     * Anything else assumes bytes.
     * 
     * e.g. Byte::fromPhpIniNotation('1KB') = Byte(1)
     * e.g. Byte::fromPhpIniNotation('1K') = Byte(1024)
     * 
     * @param string $byteStr String with shorthand notation of binary units.
     * 
     * @return Byte New Byte object.
     */
    public static function fromPhpIniNotation($byteStr)
    {
        $bytes = (int) $byteStr;
        
        switch (substr($byteStr, -1)) {
            case 'K': 
            case 'k': $bytes *= self::KB;
            break;
        
            case 'M': 
            case 'm': $bytes *= self::MB;
            break;

            case 'G': 
            case 'g': $bytes *= self::GB;
            break;
        }
        
        return new static($bytes);
    }
    
    /**
     * Returns the value of this Byte as a float after conversion of the bytes
     * to the other binary unit.
     * 
     * @param string $unit Binary unit in ISO/IEC 80000 or JEDEC standard.
     * @param int $precision The number of decimals in the result.
     * 
     * @return float The result of conversion of the bytes to the other binary unit.
     * 
     * @throws ByteParseException If the given binary unit cannot be recognised.
     */
    public function floatValue($unit, $precision = 2)
    {
        $byteValue = static::byteValueOfUnit($unit);
        
        if ($byteValue) {
            return (float) round($this->value / $byteValue, $precision);
            
        } else {
            throw new ByteParseException('The given binary unit cannot be recognised.');
        }
    }

    /**
     * Sets the Byte instance value to the specified value.
     * Returns the Byte instance for method chaining
     * 
     * @param int $value The value to be represented by the Byte.
     * 
     * @return Byte This instance.
     */
    public function setValue($value)
    {
        $this->value = $value;
        
        return $this;
    }
    
    /**
     * Returns the value of this Byte.
     * 
     * @return int The numeric value represented by this object.
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Returns the value of this Byte as a string.
     * 
     * @return string The numerical value of this Byte converted to a string.
     */
    public function __toString()
    {
        return (string) $this->value;
    }
    
    /**
     * Returns number of bytes for the given binary unit. 
     * Returns false if the unit cannot be recognised.
     * 
     * @param string $unit Binary unit in ISO/IEC 80000 or JEDEC standard.
     * 
     * @return boolean|int Number of bytes for the given unit or false.
     */
    protected static function byteValueOfUnit($unit)
    {
        switch (trim($unit)) {
            case 'KB':
            case 'KiB': return self::KB;
            
            case 'MB':
            case 'MiB': return self::MB;
                
            case 'GB':
            case 'GiB': return self::GB;
                
            case 'TB':
            case 'TiB': return self::TB;
                
            case 'B': return 1;
                
            default: return false;
        }
    }
}
