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
use SemelaPavel\Object\Byte;
use SemelaPavel\Object\Exception\ByteParseException;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class ByteTest extends TestCase
{
    public function testFrom()
    {
        $this->assertSame(10, (Byte::from(10, 'B'))->getValue());
        $this->assertSame(1024, (Byte::from(1, 'KB'))->getValue());
        $this->assertSame(1536, (Byte::from(1.5, 'KiB'))->getValue());
        $this->assertSame(1048576, (Byte::from(1, 'MiB'))->getValue());
        $this->assertSame(1363148, (Byte::from(1.3, 'MB'))->getValue());
        $this->assertSame(1073741824, (Byte::from(1, 'GB'))->getValue());
        $this->assertSame(1181116006, (Byte::from(1.1, 'GiB'))->getValue());
        $this->assertSame(1099511627776, (Byte::from(1, 'TiB'))->getValue());
        $this->assertSame(1319413953331, (Byte::from(1.2, 'TB'))->getValue());
    }
    
    public function testFromException()
    {
        $this->expectException(ByteParseException::class);
        Byte::from(1, 'km');
    }
    
    public function testParse()
    {
        $this->assertSame(10, (Byte::parse('10'))->getValue());
        $this->assertSame(10, (Byte::parse('  10B'))->getValue());
        $this->assertSame(1024, (Byte::parse(' 1  KB'))->getValue());
        $this->assertSame(1536, (Byte::parse('1.5KiB'))->getValue());
        $this->assertSame(1048576, (Byte::parse('1MiB'))->getValue());
        $this->assertSame(1363148, (Byte::parse('1.3 MB'))->getValue());
        $this->assertSame(1073741824, (Byte::parse('1 GB'))->getValue());
        $this->assertSame(1181116006, (Byte::parse('1,1GiB'))->getValue());
        $this->assertSame(1099511627776, (Byte::parse(' 1TiB '))->getValue());
        $this->assertSame(1319413953331, (Byte::parse('1,2  TB'))->getValue());
    }
    
    public function testParseException()
    {
        $this->expectException(ByteParseException::class);
        Byte::parse('1km');
    }
    
    public function testFromPhpIniNotation()
    {
        $this->assertSame(10, (Byte::fromPhpIniNotation('10'))->getValue());
        $this->assertSame(10240, (Byte::fromPhpIniNotation('10K'))->getValue());
        $this->assertSame(10, (Byte::fromPhpIniNotation('10KB'))->getValue());
        $this->assertSame(10240, (Byte::fromPhpIniNotation('10 k'))->getValue());
        $this->assertSame(10, (Byte::fromPhpIniNotation('10MB'))->getValue());
        $this->assertSame(1048576, (Byte::fromPhpIniNotation('1.5 M'))->getValue());
        $this->assertSame(1048576, (Byte::fromPhpIniNotation('1.5m'))->getValue());
        $this->assertSame(1073741824, (Byte::fromPhpIniNotation('1G'))->getValue());
        $this->assertSame(1073741824, (Byte::fromPhpIniNotation('1,5 g'))->getValue());
        $this->assertSame(10, (Byte::fromPhpIniNotation('10GB'))->getValue());
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
    
    public function testFloatValueException()
    {
        $this->expectException(ByteParseException::class);
        $byte = new Byte(524281337);
        $byte->floatValue('km');
    }
    
    public function testSet()
    {
        $byte = new Byte(1024);
        $this->assertSame(2048, $byte->setValue(2048)->getValue());
    }
    
    public function testToString()
    {
        $byte = new Byte(524281337);
        $this->assertSame('524281337', (string) $byte);
        $this->assertSame('524281337 B', $byte . ' B');
    }
}
