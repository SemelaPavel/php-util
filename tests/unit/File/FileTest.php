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
use org\bovigo\vfs\vfsStream;
use SemelaPavel\File\File;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\File\File
 * @uses \org\bovigo\vfs\vfsStream
 */
final class FileTest extends TestCase
{
    const RESERVED_FILENAMES = [
        'CON','PRN','AUX','NUL',
        'COM1','COM2','COM3','COM4','COM5','COM6','COM7','COM8','COM9',
        'LPT1','LPT2','LPT3','LPT4','LPT5','LPT6','LPT7','LPT8','LPT9'
    ];
    
    const RESERVED_CHARS = [
        '\\', '/', '|', '?', '*', '+',
        '(', ')', '{', '}', '[', ']', '<', '>',
        '!', '@', '#', '$', '%', '&', '=', ':',
        '~', '`', '^', ',', ';', '"', "'"
    ];
    
    const FILE_CONTENT = 'Some random content of the virtual file.';
    protected $file;
    
    protected function setUp(): void
    {
        $fileSystem = vfsStream::setup('root');
        $vfsFile = vfsStream::newFile('file.txt');
        $vfsFile->setContent(self::FILE_CONTENT);
        $fileSystem->addChild($vfsFile);
        
        $this->file = new File($vfsFile->url());
    }
    
    public function testGetContentsFileNotFoundException()
    {
        $this->expectException(SemelaPavel\File\Exception\FileNotFoundException::class);
        
        $file = new File('nonexistentfile.txt');
        $file->getContents();
    }
    
    public function testGetContentsFileException()
    {
        $this->expectException(SemelaPavel\File\Exception\FileException::class);
        $pattern = '/contents of the file(.*)cannot be read(.*)failed to open stream(.*)/i';
        $this->expectExceptionMessageMatches($pattern);
        
        @chmod($this->file->getPathname(), 0000);
        $this->file->getContents();
    }
    
    public function testGetContents()
    {
        $this->assertSame(self::FILE_CONTENT, $this->file->getContents());
    }
    
    public function testGetMimeType()
    {
        $invFile = new File('nonexistentfile.txt');
        
        $this->assertSame('text/plain', $this->file->getMimeType());
        $this->assertSame(null, $invFile->getMimeType());
    }
    
    public function fileNamesProvider()
    {
        return [
            ['file.txt', true],
            [' ', false],
            [' . . ', false],
            ['-file.txt', false],
            ['COM3.txt', false],
            ['file?.txt', false]
        ];
    }
    
    /**
     * @dataProvider fileNamesProvider
     */
    public function testHasValidName($fileName, $isValid)
    {
        $file = new File($fileName);
        $this->assertSame($isValid, $file->hasValidName());
    }

    public function fileNameToRTrimProvider()
    {
        return [
            ['file.txt\ '],
            ['file.txt.'],
            ["file.txt.\xFF"],
            ["file.txt.\x7F\\"],
            ['file.txt    '],
            ["file.txt.\x00\x0B"],
            ["file.txt\ \x1F"],
            ['file.txt' . DIRECTORY_SEPARATOR]
        ];
    }
    
    /**
     * @dataProvider fileNameToRTrimProvider
     */
    public function testRtrimFileName($fileName)
    {
        $this->assertSame('file.txt', File::rtrimFileName($fileName));
    }
    
    public function testRtrimFileNameASCII()
    {
        for ($i = 0; $i < 32; $i++) {
            $this->assertSame('file.txt', File::rtrimFileName('file.txt' . chr($i)));
        }
    }
    
    public function longFileNameProvider()
    {
        return [
            'too long name' => [str_repeat('a', File::MAX_FILENAME_LENGTH) . 'a', false],
            'max length name' => [substr(str_repeat('žščťď', 51), 0, File::MAX_FILENAME_LENGTH), true]
        ];
    }
    
    /**
     * @dataProvider longFileNameProvider
     */
    public function testIsValidFileNameFilenameLength($fileName, $isValid)
    {
        $this->assertSame($isValid, File::isValidFileName($fileName));
    }
    
    public function testIsValidFileName()
    {
        // Reserved file names test
        foreach (self::RESERVED_FILENAMES as $fileName) {
            $this->assertFalse(File::isValidFileName($fileName));
            $this->assertFalse(File::isValidFileName($fileName . ' '));
            $this->assertFalse(File::isValidFileName($fileName . '.txt'));
            $this->assertFalse(File::isValidFileName($fileName . '..txt'));
            $this->assertFalse(File::isValidFileName($fileName . ' .txt'));
            $this->assertFalse(File::isValidFileName($fileName . '. txt'));
            $this->assertTrue(File::isValidFileName('     ' . $fileName));
            $this->assertTrue(File::isValidFileName($fileName . '_.txt'));
            $this->assertTrue(File::isValidFileName($fileName . $fileName));
        }
        
        // Reserved characters test
        foreach (self::RESERVED_CHARS as $char) {
            $this->assertFalse(File::isValidFileName('file' . $char . '.txt'));
            $this->assertFalse(File::isValidFileName('file.txt' . $char));
            $this->assertFalse(File::isValidFileName($char . 'file.txt'));
        }
        
        // The ASCII control characters (0-31, 127, 255) test
        for ($i = 0; $i < 32; $i++) {
            $this->assertFalse(File::isValidFileName('file' . chr($i) . '.txt'));
            $this->assertFalse(File::isValidFileName('file.txt' . chr($i)));
            $this->assertFalse(File::isValidFileName(chr($i) . 'file.txt'));
        }
        
        $this->assertFalse(File::isValidFileName('file' . chr(127) . '.txt'));
        $this->assertFalse(File::isValidFileName('file.txt' . chr(127)));
        $this->assertFalse(File::isValidFileName(chr(127) . 'file.txt'));
        
        $this->assertFalse(File::isValidFileName('file' . chr(255) . '.txt'));
        $this->assertFalse(File::isValidFileName('file.txt' . chr(255)));
        $this->assertFalse(File::isValidFileName(chr(255) . 'file.txt'));
        
       /*
        * File name MUST NOT be empty or composed only of spaces.
        * File name MUST NOT be composed only of dots.
        * File name MUST NOT start with hyphen.
        */
        $this->assertFalse(File::isValidFileName(''));
        $this->assertFalse(File::isValidFileName('  '));
        $this->assertFalse(File::isValidFileName('.'));
        $this->assertFalse(File::isValidFileName('..'));
        $this->assertFalse(File::isValidFileName(' .'));
        $this->assertFalse(File::isValidFileName(' .  . '));
        $this->assertFalse(File::isValidFileName('-file.txt'));
        $this->assertTrue(File::isValidFileName('.file'));
        $this->assertTrue(File::isValidFileName(' -file.txt'));
    }
}
