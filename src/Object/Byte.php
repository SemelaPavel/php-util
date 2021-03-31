<?php declare (strict_types = 1);
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
 * Please note that MAX_VALUE depends on the system architecture (32 bit or 64 bit).
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class Byte
{
    /**
     * The value is usually int(2147483647) in 32 bit systems
     * and int(9223372036854775807) in 64 bit systems.
     */
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
    
    protected int $value;

    /**
     * Returns new Byte object that represents the specified byte value.
     * 
     * @param int $value The value to be represented by the Byte.
     * 
     * @throws \RangeException If the given value is out of range.
     */
    public function __construct(int $value)
    {
        $this->setValue($value);
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
     * @throws \RangeException If the given value is out of range.
     */
    public static function from(float $value, string $unit): Byte
    {
        $byteValue = $value * static::byteValueOfUnit($unit);
        
        if ($byteValue > self::MAX_VALUE) {
            throw new \RangeException('The value is out of range.');
        }
        
        return new static((int) $byteValue);
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
     * @throws \RangeException If the parsed byte value is out of range.
     */
    public static function parse(string $byteStr): Byte
    {
        $int = '^(0|[1-9][0-9]*)$';
        $b = '^(0|[1-9][0-9]*)\s*(B)$';
        $kmgt = '^((?:0|[1-9][0-9]*)(?:[\.\,][0-9]+)?)\s*([KMGT][i]?B)$';
        $matches = [];
        
        preg_match("/(?|{$kmgt}|{$b}|{$int})/", trim($byteStr), $matches);
        
        if (count($matches) == 3) {
            return static::from((float) str_replace(',', '.', $matches[1]), $matches[2]);
            
        } elseif (count($matches) == 2) {
            return static::from((float) $matches[1], 'B');
             
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
    public static function fromPhpIniNotation(string $byteStr): Byte
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
    public function floatValue(string $unit, int $precision = 2): float
    {
        $byteValue = static::byteValueOfUnit($unit);
        
        return (float) round($this->value / $byteValue, $precision);
    }

    /**
     * Sets the Byte instance value to the specified value. Returns the Byte
     * instance for method chaining. 
     * 
     * @param int $value The value to be represented by the Byte.
     * 
     * @return Byte This instance.
     * 
     * @throws \RangeException If the given value is out of range.
     */
    public function setValue(int $value): Byte
    {
        if ($value < self::MIN_VALUE) {
            throw new \RangeException('The value is out of range.');
        }
        
        $this->value = $value;
        
        return $this;
    }
    
    /**
     * Returns the value of this Byte.
     * 
     * @return int The numeric value represented by this object.
     */
    public function getValue(): int
    {
        return $this->value;
    }
    
    /**
     * Returns the value of this Byte as a string.
     * 
     * @return string The numerical value of this Byte converted to a string.
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Returns number of bytes for the given binary unit. 
     * Returns false if the unit cannot be recognised.
     * 
     * @param string $unit Binary unit in ISO/IEC 80000 or JEDEC standard.
     * 
     * @return int Number of bytes for the given unit or false.
     * 
     * @throws ByteParseException If the given binary unit cannot be recognised.
     */
    protected static function byteValueOfUnit(string $unit): int
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
                
            default: throw new ByteParseException('The given binary unit cannot be recognised.');
        }
    }
}
