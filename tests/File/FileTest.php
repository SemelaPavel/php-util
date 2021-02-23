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
use org\bovigo\vfs\vfsStream;
use SemelaPavel\File\File;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class FileTest extends TestCase
{
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
    
    /**
     * @see FileTest::testIsValidFileName()
     */
    public function testHasValidName()
    {
        $invFile1 = new File(' ');
        $invFile2 = new File(' . . ');
        $invFile3 = new File('-file.txt');
        $invFile4 = new File('COM3.txt');
        $invFile5 = new File('file?.txt');
        
        $this->assertTrue($this->file->hasValidName());
        
        $this->assertFalse($invFile1->hasValidName());
        $this->assertFalse($invFile2->hasValidName());
        $this->assertFalse($invFile3->hasValidName());
        $this->assertFalse($invFile4->hasValidName());
        $this->assertFalse($invFile5->hasValidName());
    }
    
    public function testRtrimFileName()
    {
        $this->assertSame('file.txt', File::rtrimFileName('file.txt\ '));
        $this->assertSame('file.txt', File::rtrimFileName('file.txt.'));
        $this->assertSame('file.txt', File::rtrimFileName("file.txt.\xFF"));
        $this->assertSame('file.txt', File::rtrimFileName("file.txt.\x7F\\"));
        $this->assertSame('file.txt', File::rtrimFileName('file.txt    '));
        $this->assertSame('file.txt', File::rtrimFileName("file.txt.\x00\x0B"));
        $this->assertSame('file.txt', File::rtrimFileName("file.txt\ \x1F"));
        $this->assertSame('file.txt', File::rtrimFileName('file.txt' . DIRECTORY_SEPARATOR));
        
        for ($i = 0; $i < 32; $i++) {
            $this->assertSame('file.txt', File::rtrimFileName('file.txt' . chr($i)));
        }
    }
    
    public function testIsValidFileName()
    {
        //File name length test
        $longFileName = str_repeat('a', File::MAX_FILENAME_LENGTH) . 'a';
        $maxLengthFileName = substr(str_repeat('žščťď', 51), 0, File::MAX_FILENAME_LENGTH);
        
        $this->assertFalse(File::isValidFileName($longFileName));
        $this->assertTrue(File::isValidFileName($maxLengthFileName));
        
        // Reserved file names test
        foreach (File::RESERVED_FILENAMES as $fileName) {
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
        foreach (File::RESERVED_CHARS as $char) {
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
