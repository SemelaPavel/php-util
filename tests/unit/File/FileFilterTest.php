<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\UnitTests\File;

use \PHPUnit\Framework\TestCase;
use \SemelaPavel\File\FileFilter;
use \SemelaPavel\String\RegexPattern;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\File\FileFilter
 * @uses \SemelaPavel\String\RegexPattern
 */
final class FileFilterTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testConstruct(): FileFilter
    {
        $filter = new FileFilter();
        $filter->setFileNameWhiteList(['*.jpg', '*.png', '*.gif']);
        $filter->setFileNameBlackList(['*.php.*']);
        $filter->setFileNameRegex(new RegexPattern('^[^0-9]*$'));
        $filter->setFileSize(1024);
        $filter->setMTime(new \DateTime('2021-01-01'));
        
        return $filter;
    }
    
    /**
     * @depends testConstruct
     */
    public function testFullFileFilter(FileFilter $filter): void
    {
        $this->assertTrue($filter->fileNameMatch('image.jpg'));
        $this->assertTrue($filter->fileNameMatch('image.png'));
        $this->assertTrue($filter->fileNameMatch('image.gif'));
        
        $this->assertFalse($filter->fileNameMatch('image.php.jpg'));
        $this->assertFalse($filter->fileNameMatch('image1.jpg'));
        $this->assertFalse($filter->fileNameMatch('image2.png'));
        $this->assertFalse($filter->fileNameMatch('image3.gif'));
        
        $this->assertTrue($filter->compareFileSize(1024));
        $this->assertFalse($filter->compareFileSize(2048));
        
        $this->assertTrue($filter->compareMTime('2021-01-01'));
        $this->assertFalse($filter->compareMTime('2021-01-01 12:00'));
    }
    
    public function fileNameMatchProvider(): array
    {
        return [
            'white list' =>
                [(new FileFilter())->setFileNameWhiteList(['*.jpg', '*.png', '*.gif']), [
                    ['image.jpg' => true],
                    ['image.png' => true],
                    ['image.gif' => true],
                    ['image.jpeg' => false]
                ]],
            
            'black list' =>
                [(new FileFilter())->setFileNameBlackList(['*.php.*']), [
                    ['image.gif' => true],
                    ['image.php.jpg' => false]
                ]],
            
            'regex pattern' =>
                [(new FileFilter())->setFileNameRegex(new RegexPattern('^[^0-9]*$')), [
                    ['image.png' => true],
                    ['image1.jpg' => false]
                ]]
        ];
    }
    
    /**
     * @dataProvider fileNameMatchProvider
     */
    public function testFileNameMatch(FileFilter $filter, array $fileNames): void
    {
        foreach ($fileNames[0] as $fileName => $result) {
            $this->assertSame($result, $filter->fileNameMatch($fileName));
        }
    }
    
    public function fileSizeProvider(): array
    {
        return [
            ['   1024 ', [
                [1024 => true],
                [1023 => false]
            ]],
            ['= 1KB ', [
                [1024 => true],
                [1023 => false]
            ]],
            [' > 1 KB', [
                [1025 => true],
                [1024 => false]
            ]],
            [' > 1024 < 1MB ', [
                [1048575 => true],
                [1023 => false],
                [1048576 => false]
            ]],
            ['<>   1,5   KB',[
               [1537 => true],
               [1535 => true],
               [1536 => false]
            ]],
            ['>= 1024 ', [
                [1024 => true],
                [1025 => true],
                [1023 => false]
            ]],
            [' <= 1 KB', [
                [1024 => true],
                [1023 => true],
                [1025 => false]
            ]]
            
        ];
    }
    
    /**
     * @dataProvider fileSizeProvider
     */
    public function testCompareFileSize(string $predicate, array $fileSizes): void
    {
        $filter = (new FileFilter())->setFileSize($predicate);
        foreach ($fileSizes[0] as $fileSize => $result) {
            $this->assertSame($result, $filter->compareFileSize($fileSize));
        }
    }

    public function mTimeProvider(): array
    {
        return [
            ['   2021-01-01 12:00 ', [
                [' 2021-01-01  12:00 ' => true],
                ['2021-01-01' => false]
            ]],
            ['= 2021-01-01    12:00 ', [
                ['2021-01-01 12:00' => true],
                ['2021-01-01' => false]
            ]],
            [' >2021-01-01',[
                ['2021-01-01 12:00' => true],
                ['2021-01-02' => true],
                ['2020-12-31' => false]
            ]],
            [' > 2021-01-01   12:00 < 2021-01-01 23:59 ', [
                ['2021-01-01 13:00' => true],
                ['2021-01-01 11:00' => false],
                ['2021-01-02' => false]
            ]],
            ['<>   2021-01-01', [
                ['2021-02-01' => true],
                ['2021-01-01 12:00' => true],
                ['2020-12-31' => true],
                ['2021-01-01' => false]
            ]],
            ['>=2021-01-01 ', [
                ['2021-01-02' => true],
                ['2021-01-01 12:00' => true],
                ['2021-01-01' => true],
                ['2020-12-31' => false]
            ]],
            [' <= 2021-01-01 12:00', [
                ['2021-01-01' => true],
                ['2021-01-01 10:00' => true],
                ['2021-01-01 12:00' => true],
                ['2021-01-01 13:00' => false]
            ]],
            [$time = \time(), [
                [$time => true],
                [$time + 1 => false]
            ]],
            ['> ' . $time, [
                [$time + 1 => true],
                [$time => false]
            ]]
        ];
    }
    
    /**
     * @dataProvider mTimeProvider
     * 
     * @param string|int $predicate
     */
    public function testCompareMTime($predicate, array $mTimes): void
    {
        $filter = (new FileFilter())->setMTime($predicate);
        foreach ($mTimes[0] as $mTime => $result) {
            $this->assertSame($result, $filter->compareMTime($mTime));
        }
    }
    
    public function testSetFileSizeArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/predicate format/i');
        (new FileFilter())->setFileSize('!= 1 MB');
    }
    
    public function testSetFileSizeByteException(): void
    {
        $this->expectException(\SemelaPavel\Object\Exception\ByteParseException::class);
        (new FileFilter())->setFileSize('> 1 K');
    }
    
    public function testSetMTimeArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/predicate format/i');
        (new FileFilter())->setMTime('!= 2021-01-01');
    }
    
    public function testSetMTimeDateException(): void
    {
        $this->expectException(\SemelaPavel\Time\Exception\DateTimeParseException::class);
        (new FileFilter())->setMTime('> 0000 00 00');
    }
}
