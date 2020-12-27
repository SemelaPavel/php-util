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
    
    protected function isUploadedFile()
    {
        return $this->isUploadedFile;
    }
    
    protected function moveUploadedFile($sourcePathName, $targetPathName)
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
        
    public function getPathName()
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
     * @see FileTest::testIsValidFileName()
     */
    public function testHasValidName()
    {
        $vldFile1 = new UploadedFile(self::PATH1, 'file.txt', self::SIZE1, self::UERR1);
        
        $invFile1 = new UploadedFile(self::PATH1, ' ', self::SIZE1, self::UERR1);
        $invFile2 = new UploadedFile(self::PATH1, ' . . ', self::SIZE1, self::UERR1);
        $invFile3 = new UploadedFile(self::PATH1, '-file.txt', self::SIZE1, self::UERR1);
        $invFile4 = new UploadedFile(self::PATH1, 'COM3.txt', self::SIZE1, self::UERR1);
        $invFile5 = new UploadedFile(self::PATH1, 'file?.txt', self::SIZE1, self::UERR1);
        $invFile6 = new UploadedFile(self::PATH1, "file.php\x00.txt", self::SIZE1, self::UERR1);
        
        $this->assertTrue($vldFile1->hasValidName());

        $this->assertFalse($invFile1->hasValidName());
        $this->assertFalse($invFile2->hasValidName());
        $this->assertFalse($invFile3->hasValidName());
        $this->assertFalse($invFile4->hasValidName());
        $this->assertFalse($invFile5->hasValidName());
        $this->assertFalse($invFile6->hasValidName());
    }
   
    public function testMoveIniFileSizeException()
    {
        $this->expectException(SemelaPavel\Http\Exception\IniFileSizeException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_INI_SIZE);
        $this->expectExceptionMessageMatches('/upload_max_filesize/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_INI_SIZE);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveFormFileSizeException()
    {
        $this->expectException(SemelaPavel\Http\Exception\FormFileSizeException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_FORM_SIZE);
        $this->expectExceptionMessageMatches('/MAX_FILE_SIZE/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_FORM_SIZE);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMovePartialFileException()
    {
        $this->expectException(SemelaPavel\Http\Exception\PartialFileException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_PARTIAL);
        $this->expectExceptionMessageMatches('/partially/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_PARTIAL);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveNoFileUploadedException()
    {
        $this->expectException(SemelaPavel\Http\Exception\NoFileUploadedException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_NO_FILE);
        $this->expectExceptionMessageMatches('/No file/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_NO_FILE);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveNoTmpDirException()
    {
        $this->expectException(SemelaPavel\Http\Exception\NoTmpDirException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_NO_TMP_DIR);
        $this->expectExceptionMessageMatches('/temporary folder/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_NO_TMP_DIR);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveFileWriteException()
    {
        $this->expectException(SemelaPavel\Http\Exception\FileWriteException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_CANT_WRITE);
        $this->expectExceptionMessageMatches('/could not be written/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_CANT_WRITE);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveUploadStoppedException()
    {
        $this->expectException(SemelaPavel\Http\Exception\UploadStoppedException::class);
        $this->expectExceptionCode(\UPLOAD_ERR_EXTENSION);
        $this->expectExceptionMessageMatches('/extension/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, \UPLOAD_ERR_EXTENSION);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveFileUploadException()
    {
        $unknownErrorCode = 111111;
        $this->expectException(SemelaPavel\Http\Exception\FileUploadException::class);
        $this->expectExceptionCode($unknownErrorCode);
        $this->expectExceptionMessageMatches('/unknown error/i');
        
        $invFile = new MockUploadedFile(self::PATH2, self::NAME2, self::SIZE2, $unknownErrorCode);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveInvalidFileNameException1()
    {
        $this->expectException(SemelaPavel\File\Exception\InvalidFileNameException::class);
        
        $invFile = new MockUploadedFile(self::PATH2, "file.php\x00.txt", self::SIZE2, \UPLOAD_ERR_OK);
        $invFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveInvalidFileNameException2()
    {
        $this->expectException(SemelaPavel\File\Exception\InvalidFileNameException::class);
        
        $invFile = $this->getValidMockFile();
        $invFile->move($this->getVfs()->getChild('upload')->url(), "file/.txt");
    }
    
    public function testMovePrepareTargetDirectoryFileException1()
    {
        $this->expectException(SemelaPavel\File\Exception\FileException::class);
        $this->expectExceptionMessageMatches('/Failed to create/i');
        
        $vldFile = $this->getValidMockFile();
        $vldFile->move($this->getVfs()->getChild('upload/not_writable')->url() . '/new');
    }
    
    public function testMovePrepareTargetDirectoryFileException2()
    {
        $this->expectException(SemelaPavel\File\Exception\FileException::class);
        $this->expectExceptionMessageMatches('/Unable to write/i');
        
        $vldFile = $this->getValidMockFile();
        $vldFile->move($this->getVfs()->getChild('upload/not_writable')->url());
    }
    
    public function testMoveMoveUploadedFileException1()
    {
        $this->expectException(SemelaPavel\Http\Exception\FileUploadException::class);
        $this->expectExceptionMessageMatches('/could not be moved(.*)not a valid uploaded file/i');
        
        $vldFile = $this->getValidMockFile();
        $vldFile->moveUploadedFileSuccess = false;
        $vldFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMoveMoveUploadedFileException2()
    {
        $this->expectException(SemelaPavel\Http\Exception\FileUploadException::class);
        $this->expectExceptionMessageMatches('/could not be moved(.*)' . MockUploadedFile::NOT_MOVED_WARNING . '/i');
        
        $vldFile = $this->getValidMockFile();
        $vldFile->moveUploadedFileSuccess = false;
        $vldFile->moveUploadedFileWarning = true;
        $vldFile->move($this->getVfs()->getChild('upload')->url());
    }
    
    public function testMove1()
    {
        $uplDir = $this->getVfs()->getChild('upload');
        $vldFile = $this->getValidMockFile();
       
        $file = $vldFile->move($uplDir->url());
        $vfsFile = $uplDir->getChild(self::NAME2);
        
        $this->assertTrue($file instanceof SemelaPavel\File\File);
        $this->assertTrue(is_file($file->getPathname()));
        $this->assertSame(0644, $vfsFile->getPermissions());
    }
    
    public function testMove2()
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
