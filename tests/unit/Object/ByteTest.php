<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use SemelaPavel\Object\Byte;
use SemelaPavel\Object\Exception\ByteParseException;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Object\Byte
 * @uses \SemelaPavel\Object\Exception\ByteParseException
 */
final class ByteTest extends TestCase
{
    public function byteFromUnitProvider()
    {
        // input value, unit, output bytes
        return [
            [ 0.0,    'B', 0],
            [  0,     'B', 0],
            [ 10,     'B', 10],
            [  1,    'KB', 1024],
            [  1.5, 'KiB', 1536],
            [  1,   'MiB', 1048576],
            [  1.3,  'MB', 1363148],
            [  1,    'GB', 1073741824],
            [  1.1, 'GiB', 1181116006],
            [  1,   'TiB', 1099511627776],
            [  1.2,  'TB', 1319413953331],
        ];
    }
    
    public function byteFromUnitParseExceptionProvider()
    {
        return [
            [1, ''], [1, 'Byte'], [1, 'b'],
            [1, 'K B'], [1, 'Gb'], [1, 'tB'],
            [1, 'tib'], [1, 'MIB'], [1, 'GIB']
        ];
    }
    
    public function byteFromUnitRangeExceptionProvider()
    {
        return [[-1], [PHP_FLOAT_MAX]];
    }
    
    public function parseProvider()
    {
        return [
            [0, '0'],
            [10, '10'],
            [10, '  10B'],
            [1024, ' 1  KB'],
            [1536, '1.5KiB'],
            [1048576, '1MiB'],
            [1363148, '1.3 MB'],
            [1073741824, '1 GB'],
            [1181116006, '1,1GiB'],
            [1099511627776, ' 1TiB '],
            [1319413953331, '1,2  TB']
        ];
    }
    
    public function parseByteParseExceptionProvider()
    {
        return [
            [''],
            ['-1'],
            ['1 Byte'],
            ['1 b'],
            ['1 K B'],
            ['1 Gb'],
            ['1 tB'],
            ['1 tib'],
            ['1 MIB'],
            ['1 GIB']
        ];
    }
    
    public function parseRangeExceptionProvider()
    {
        return [
            ['9223372036854775808'],
            ['9999999999 GiB']
        ];
    }

    public function phpIniNotationProvider()
    {
        return [
            [0, '0'],
            [10, '10'],
            [10240, '10K'],
            [10, '10KB'],
            [10240, '10 k'],
            [10, '10MB'],
            [1048576, '1.5 M'],
            [1048576, '1.5m'],
            [1073741824, '1G'],
            [1073741824, '1,5 g'],
            [10, '10GB']
        ];
    }
    
    /**
     * @dataProvider byteFromUnitProvider
     */
    public function testFrom($input, $unit, $output)
    {
        $this->assertSame($output, (Byte::from($input, $unit))->getValue());

    }

    /**
     * @dataProvider byteFromUnitParseExceptionProvider
     */
    public function testFromByteParseException($value, $unit)
    {
        $this->expectException(ByteParseException::class);
        Byte::from($value, $unit);
    }
 
    /**
     * @dataProvider byteFromUnitRangeExceptionProvider
     */
    public function testFromRangeException($value)
    {
        $this->expectException(\RangeException::class);
        Byte::from($value, 'B');
    }
    
    /**
     * @dataProvider parseProvider
     */
    public function testParse($result, $input)
    {
        $this->assertSame($result, (Byte::parse($input))->getValue());
        
    }
      
    /**
     * @dataProvider parseByteParseExceptionProvider
     */
    public function testParseException($value)
    {
        $this->expectException(ByteParseException::class);
        Byte::parse($value);
    }
    
    /**
     * @dataProvider parseRangeExceptionProvider
     */
    public function testParseRangeException($value)
    {
        $this->expectException(\RangeException::class);
        Byte::parse($value);
    }
    
    /**
     * @dataProvider phpIniNotationProvider
     */
    public function testFromPhpIniNotation($result, $input)
    {
        $this->assertSame($result, (Byte::fromPhpIniNotation($input))->getValue());
    }
    
    public function testFloatValue()
    {
        $byte = new Byte(524281337);
        $this->assertSame(524281337.0, $byte->floatValue('B'));
        $this->assertSame(511993.49, $byte->floatValue('KB'));
        $this->assertSame(511993.4932, $byte->floatValue('KiB', 4));
        $this->assertSame(499.994, $byte->floatValue(' MB ', 3));
        $this->assertSame(499.99, $byte->floatValue('MiB'));
        $this->assertSame(0.49, $byte->floatValue('GB'));
        $this->assertSame(0.488, $byte->floatValue('  GiB ', 3));
        $this->assertSame(0.0004768311, $byte->floatValue('TB', 10));
        $this->assertSame(0.0004768311, $byte->floatValue('TiB', 10));
    }
    
    /**
     * @dataProvider byteFromUnitParseExceptionProvider
     */
    public function testFloatValueException($byteValue, $unit)
    {
        $this->expectException(ByteParseException::class);
        $byte = new Byte($byteValue);
        $byte->floatValue($unit);
    }
    
    /**
     * @dataProvider parseProvider
     */
    public function testSet($value)
    {
        $byte = new Byte(1);
        $this->assertSame($value, $byte->setValue($value)->getValue());
    }

    public function testSetRangeException()
    {
        $this->expectException(\RangeException::class);
        $byte = new Byte(1);
        $byte->setValue(-1);
    }
    
    public function testToString()
    {
        $byte = new Byte(524281337);
        $this->assertSame('524281337', (string) $byte);
        $this->assertSame('524281337 B', $byte . ' B');
    }
}
