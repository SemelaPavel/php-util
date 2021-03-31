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
use SemelaPavel\Http\UploadedFile;
use org\bovigo\vfs\vfsStream;
            
/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class MockUploadedFile extends UploadedFile
{
    /**
     * A string representing E_USER_WARNING of the move_uploaded_file function.
     */
    const NOT_MOVED_WARNING = 'move_uploaded_file warning';
    
    /**
     * @var bool True if pretend a file uploaded via HTTP.  
     */
    public $isUploadedFile = true;
    
    /**
     * @var bool True if pretend successful move of the uploaded file. 
     */
    public $moveUploadedFileSuccess = true;
    
    /**
     * @var bool True if a warning when moving uploading file should be triggered.
     */
    public $moveUploadedFileWarning = false;
    
    protected function isUploadedFile(): bool
    {
        return $this->isUploadedFile;
    }
    
    protected function moveUploadedFile(string $sourcePathName, string $targetPathName): bool
    {
        if ($this->moveUploadedFileWarning) {
            trigger_error(self::NOT_MOVED_WARNING, E_USER_WARNING);
        }
       
        if ($this->moveUploadedFileSuccess) {
            $result = rename($sourcePathName, $targetPathName);
        } else {
            $result = false;
        }
        
        return $this->moveUploadedFileSuccess && $result;
    }
}

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Http\UploadedFile
 * @uses \org\bovigo\vfs\vfsStream
 */
final class UploadedFileTest extends TestCase
{
    const PATH1 = 'C:\tmp\123file.tmp';
    const PATH2 = 'vfs://root/tmp/123file.tmp'; // ValidMockFile tmp path
    
    const NAME1 = '../badfile.txt/'; // invalid file name
    const NAME2 = 'file.txt'; // ValidMockFile name
    
    const SIZE1 = 0;
    const SIZE2 = 2097152; // 2 MiB; ValidMockFile size
    
    const UERR1 = \UPLOAD_ERR_OK; // ValidMockFile upload error code
    const UERR2 = \UPLOAD_ERR_FORM_SIZE;
    
    protected function getFile1()
    {
        return new UploadedFile(self::PATH1, self::NAME1, self::SIZE1, self::UERR1);
    }
    
    protected function getFile2()
    {
        return new UploadedFile(self::PATH2, self::NAME2, self::SIZE2, self::UERR2);
    }
    
    protected function getValidMockFile()
    {
        return new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_OK);
    }
    
    /**
     * root/
     * |-- tmp/
     * |   |-- 123file.tmp
     * |
     * |-- upload/
     * |   |-- not_writable/
     */
    protected function getVfs()
    {
        // File system root
        $fileSystem = vfsStream::setup('root');

        // File system directories
        $tmpDir = vfsStream::newDirectory('tmp', 0777);
        $uploadDir = vfsStream::newDirectory('upload', 0777);
        $notWritableDir = vfsStream::newDirectory('not_writable', 0000);

        // File system files
        $tmpFile = vfsStream::newFile('123file.tmp');

        // Building a file system tree
        $fileSystem->addChild($tmpDir);
        $fileSystem->addChild($uploadDir);
        $tmpDir->addChild($tmpFile);
        $uploadDir->addChild($notWritableDir);
        
        return $fileSystem;
    }
    
    public function clientFileNameProvider()
    {
        return [
            ['file.txt', true],
            [' ', false],
            [' . . ', false],
            ['-file.txt', false],
            ['COM3.txt', false],
            ['file?.txt', false],
            ["file.php\x00.txt", false]
        ];
    }
    
    public function uploadErrorExceptionProvider()
    {
        return [
            [\UPLOAD_ERR_INI_SIZE, SemelaPavel\Http\Exception\IniFileSizeException::class, '/upload_max_filesize/i'],
            [\UPLOAD_ERR_FORM_SIZE, SemelaPavel\Http\Exception\FormFileSizeException::class, '/MAX_FILE_SIZE/i'],
            [\UPLOAD_ERR_PARTIAL, SemelaPavel\Http\Exception\PartialFileException::class, '/partially/i'],
            [\UPLOAD_ERR_NO_FILE, SemelaPavel\Http\Exception\NoFileUploadedException::class, '/No file/i'],
            [\UPLOAD_ERR_NO_TMP_DIR, SemelaPavel\Http\Exception\NoTmpDirException::class, '/temporary folder/i'],
            [\UPLOAD_ERR_CANT_WRITE, SemelaPavel\Http\Exception\FileWriteException::class, '/could not be written/i'],
            [\UPLOAD_ERR_EXTENSION, SemelaPavel\Http\Exception\UploadStoppedException::class, '/extension/i'],
            [123456789, SemelaPavel\Http\Exception\FileUploadException::class, '/unknown error/i']
        ];
    }
    
    public function notWritableDirectoryExceptionProvider()
    {
        // directory, FileException message regex
        return [
            [$this->getVfs()->getChild('upload/not_writable')->url() . '/new', '/Failed to create/i'],
            [$this->getVfs()->getChild('upload/not_writable')->url(), '/Unable to write/i']
        ];
    }
    
    public function fileCouldNotBeMovedExceptionProvider()
    {
        // moveUploadedFileSuccess, moveUploadedFileWarning, FileUploadException message regex
        return [
            [false, false, '/could not be moved(.*)not a valid uploaded file/i'],
            [false, true, '/could not be moved(.*)' . MockUploadedFile::NOT_MOVED_WARNING . '/i']
        ];
    }
        
    public function testGetPathName()
    {
        $this->assertSame(self::PATH1, $this->getFile1()->getPathname());
        $this->assertSame(self::PATH2, $this->getFile2()->getPathname());
    }

    public function testGetClientFilename()
    {
        $this->assertSame(self::NAME1, $this->getFile1()->getClientFilename());
        $this->assertSame(self::NAME2, $this->getFile2()->getClientFilename());
    }
    
    public function testGetSize()
    {
        $this->assertSame(self::SIZE1, $this->getFile1()->getSize());
        $this->assertSame(self::SIZE2, $this->getFile2()->getSize());
    }
    
    public function testGetError()
    {
        $this->assertSame(self::UERR1, $this->getFile1()->getError());
        $this->assertSame(self::UERR2, $this->getFile2()->getError());
    }
    
    public function testIsUploaded()
    {
        $this->assertFalse($this->getFile1()->isUploaded());
        $this->assertFalse($this->getFile2()->isUploaded());
        
        $mockFile1= new MockUploadedFile(self::PATH1, self::NAME1, self::SIZE1, \UPLOAD_ERR_OK);
        $mockFile2= new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_FORM_SIZE);

        $this->assertTrue($mockFile1->isUploaded());
        $this->assertFalse($mockFile2->isUploaded());
    }

    /**
     * @dataProvider clientFileNameProvider
     */
    public function testHasValidName($fileName, $isValid)
    {
        $file = new UploadedFile('/tmp/file.tmp', $fileName, 1024, \UPLOAD_ERR_OK);
        $this->assertSame($isValid, $file->hasValidName());
    }

    /**
     * @dataProvider uploadErrorExceptionProvider
     */
    public function testUploadErrorException($errorCode, $exception, $msg)
    {
        $this->expectException($exception);
        $this->expectExceptionMessageMatches($msg);
        
        $file = new MockUploadedFile('/tmp/file.tmp', 'file.txt', 1024, $errorCode);
        $file->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveInvalidClientFileNameException()
    {
        $this->expectException(SemelaPavel\File\Exception\InvalidFileNameException::class);
        
        $file = new MockUploadedFile('/tmp/file.tmp', "file.php\x00.txt", 1024, \UPLOAD_ERR_OK);
        $file->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveInvalidNewFileNameException()
    {
        $this->expectException(SemelaPavel\File\Exception\InvalidFileNameException::class);
        
        $file = $this->getValidMockFile();
        $file->move($this->getVfs()->getChild('upload')->url(), "file/.txt");
    }
      
    /**
     * @dataProvider notWritableDirectoryExceptionProvider
     */
    public function testExceptionMoveToNotWritableDirectory($dir, $msg)
    {
        $this->expectException(SemelaPavel\File\Exception\FileException::class);
        $this->expectExceptionMessageMatches($msg);
        
        $file = $this->getValidMockFile();
        $file->move($dir);
    }

    /**
     * @dataProvider fileCouldNotBeMovedExceptionProvider
     */
    public function testfileCouldNotBeMovedException($moveUploadedFileSuccess, $moveUploadedFileWarning, $msg)
    {
        $this->expectException(SemelaPavel\Http\Exception\FileUploadException::class);
        $this->expectExceptionMessageMatches($msg);
        
        $file = $this->getValidMockFile();
        $file->moveUploadedFileSuccess = $moveUploadedFileSuccess;
        $file->moveUploadedFileWarning = $moveUploadedFileWarning;
        $file->move($this->getVfs()->getChild('upload')->url());
    }
    
    /**
     * Tests moving of a valid uploaded file to the upload directory.
     */
    public function testMoveToUploadDir()
    {
        $uplDir = $this->getVfs()->getChild('upload');
        $vldFile = $this->getValidMockFile();
       
        $file = $vldFile->move($uplDir->url());
        $vfsFile = $uplDir->getChild(self::NAME2);
        
        $this->assertTrue($file instanceof SemelaPavel\File\File);
        $this->assertTrue(is_file($file->getPathname()));
        $this->assertSame(0644, $vfsFile->getPermissions());
    }
    
    /**
     * Tests moving of a valid uploaded file to the newly created directory.
     */
    public function testMoveToNewDir()
    {
        $uplDir = $this->getVfs()->getChild('upload');
        $vldFile = $this->getValidMockFile();
        $newDir = 'new/txt/today';
        $newFileName = 'document.pdf';

        $file = $vldFile->move($uplDir->url() . DIRECTORY_SEPARATOR . $newDir . '/', $newFileName);
        $targetDirNew = $uplDir->getChild($newDir);
        $vfsFile = $targetDirNew->getChild($newFileName);
        
        $this->assertTrue($file instanceof SemelaPavel\File\File);
        $this->assertTrue(is_file($file->getPathname()));
        $this->assertSame(0777, $targetDirNew->getPermissions());
        $this->assertSame(0644, $vfsFile->getPermissions());
    }
}
