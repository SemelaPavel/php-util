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
use SemelaPavel\File\FileFilter;
use SemelaPavel\String\RegexPattern;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\File\FileFilter
 * @uses \SemelaPavel\String\RegexPattern
 */
final class FileFilterTest extends TestCase
{
    protected $filter;
    
    public function testFullFileFilter()
    {
        $this->filter = (new FileFilter())
            ->setFileNameWhiteList(['*.jpg', '*.png', '*.gif'])
            ->setFileNameBlackList(['*.php.*'])
            ->setFileNameRegex(new RegexPattern('^[^0-9]*$'))
            ->setFileSize(1024)
            ->setMTime(new \DateTime('2021-01-01'));
        
        $this->assertTrue($this->filter->fileNameMatch('image.jpg'));
        $this->assertTrue($this->filter->fileNameMatch('image.png'));
        $this->assertTrue($this->filter->fileNameMatch('image.gif'));
        
        $this->assertFalse($this->filter->fileNameMatch('image.php.jpg'));
        $this->assertFalse($this->filter->fileNameMatch('image1.jpg'));
        $this->assertFalse($this->filter->fileNameMatch('image2.png'));
        $this->assertFalse($this->filter->fileNameMatch('image3.gif'));
        
        $this->assertTrue($this->filter->compareFileSize(1024));
        $this->assertFalse($this->filter->compareFileSize(2048));
        
        $this->assertTrue($this->filter->compareMTime('2021-01-01'));
        $this->assertFalse($this->filter->compareMTime('2021-01-01 12:00'));
    }
    
    public function testFileNameMatch()
    {
        $filterWl = (new FileFilter())->setFileNameWhiteList(['*.jpg', '*.png', '*.gif']);
        $filterBl = (new FileFilter())->setFileNameBlackList(['*.php.*']);
        $filterRx  = (new FileFilter())->setFileNameRegex(new RegexPattern('^[^0-9]*$'));
        $this->assertTrue($filterWl->fileNameMatch('image.jpg'));
        $this->assertTrue($filterWl->fileNameMatch('image.png'));
        $this->assertTrue($filterWl->fileNameMatch('image.gif'));
        $this->assertTrue($filterBl->fileNameMatch('image.gif'));
        $this->assertTrue($filterRx->fileNameMatch('image.png'));
        $this->assertFalse($filterBl->fileNameMatch('image.php.jpg'));
        $this->assertFalse($filterRx->fileNameMatch('image1.jpg'));
    }
    
    public function testCompareFileSize()
    {
        $filterSize1 = (new FileFilter())->setFileSize('   1024 ');
        $filterSize2 = (new FileFilter())->setFileSize('= 1KB ');
        $filterSize3 = (new FileFilter())->setFileSize(' > 1 KB');
        $filterSize4 = (new FileFilter())->setFileSize(' > 1024 < 1MB ');
        $filterSize5 = (new FileFilter())->setFileSize('<>   1,5   KB');
        $filterSize6 = (new FileFilter())->setFileSize('>= 1024 ');
        $filterSize7 = (new FileFilter())->setFileSize(' <= 1 KB');
        
        $this->assertTrue($filterSize1->compareFileSize(1024));
        $this->assertTrue($filterSize2->compareFileSize(1024));
        $this->assertTrue($filterSize3->compareFileSize(1025));
        $this->assertTrue($filterSize4->compareFileSize(1048575));
        $this->assertTrue($filterSize5->compareFileSize(1537));
        $this->assertTrue($filterSize5->compareFileSize(1535));
        $this->assertTrue($filterSize6->compareFileSize(1024));
        $this->assertTrue($filterSize6->compareFileSize(1025));
        $this->assertTrue($filterSize7->compareFileSize(1024));
        $this->assertTrue($filterSize7->compareFileSize(1023));
        
        $this->assertFalse($filterSize1->compareFileSize(1023));
        $this->assertFalse($filterSize2->compareFileSize(1023));
        $this->assertFalse($filterSize3->compareFileSize(1024));
        $this->assertFalse($filterSize4->compareFileSize(1023));
        $this->assertFalse($filterSize4->compareFileSize(1048576));
        $this->assertFalse($filterSize5->compareFileSize(1536));
        $this->assertFalse($filterSize6->compareFileSize(1023));
        $this->assertFalse($filterSize7->compareFileSize(1025));
    }

    public function mTimeProvider()
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
     */
    public function testCompareMTime($predicate, $mTimes)
    {
        $filter = (new FileFilter())->setMTime($predicate);
        foreach ($mTimes[0] as $mTime => $result) {
            $this->assertSame($result, $filter->compareMTime($mTime));
        }
    }
    
    public function testSetFileSizeArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/predicate format/i');
        (new FileFilter())->setFileSize('!= 1 MB');
    }
    
    public function testSetFileSizeByteException()
    {
        $this->expectException(\SemelaPavel\Object\Exception\ByteParseException::class);
        (new FileFilter())->setFileSize('> 1 K');
    }
    
    public function testSetMTimeArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/predicate format/i');
        (new FileFilter())->setMTime('!= 2021-01-01');
    }
    
    public function testSetMTimeDateException()
    {
        $this->expectException(\SemelaPavel\Time\Exception\DateTimeParseException::class);
        (new FileFilter())->setMTime('> 0000 00 00');
    }
}
