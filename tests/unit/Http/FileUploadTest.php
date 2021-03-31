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
use SemelaPavel\Http\FileUpload;
use SemelaPavel\Http\UploadedFile;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Http\FileUpload
 * @uses \SemelaPavel\Http\UploadedFile
 */
final class FileUploadTest extends TestCase
{
    protected function setUp(): void
    {
        $_FILES =
        
        array ( 
            'fileZero' => array (
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => 4,
                'size' => 0
            ),
            'fileOne' => array (
                'name' => 'kitten.jpg.php',
                'type' => 'application/octet-stream',
                'tmp_name' => 'E:\wampserver\tmp\php56C1.tmp',
                'error' => 0,
                'size' => 17690
            ),
            'filesArray' => array (
                'name' => array (
                    '1D' => '2MB_FILE.txt',
                    '2D1' => array (
                        '2D2' => 'troll.jpg.php'
                    ),
                    '3D1' => array (
                        0 => array (
                            '3D3' => 'DVD.ods'
                        )
                    )
                ),
                'type' => array (
                    '1D' => 'text/plain',
                    '2D1' => array (
                        '2D2' => 'application/octet-stream'
                    ),
                    '3D1' => array (
                        0 => array (
                            '3D3' => 'application/vnd.oasis.opendocument.spreadsheet'
                        )
                    )
                ),
                'tmp_name' => array (
                    '1D' => 'E:\wampserver\tmp\php56D2.tmp',
                    '2D1' => array (
                        '2D2' => 'E:\wampserver\tmp\php56E2.tmp'
                    ),
                    '3D1' => array (
                        0 => array (
                            '3D3' => 'E:\wampserver\tmp\php56E3.tmp'
                        )
                    )
                ),
                'error' => array (
                    '1D' => 0,
                    '2D1' => array (
                        '2D2' => 0
                    ),
                    '3D1' => array (
                        0 => array (
                            '3D3' => 0
                        )
                    )
                ),
                'size' => array (
                    '1D' => 2097152,
                    '2D1' => array (
                        '2D2' => 15452
                    ),
                    '3D1' => array (
                        0 => array (
                            '3D3' => 24576
                        )
                    )
                )
            )
        );
        $_SERVER['CONTENT_LENGTH'] = 0;
    }

    public function testGetUploadedFilesLengthException()
    {
        $this->expectException(\LengthException::class);
        $_SERVER['CONTENT_LENGTH'] = FileUpload::maxPostSize() + 1;
        (new FileUpload())->getUploadedFiles();
    }
    
    public function testGetUploadedFiles()
    {
        $file0 = null;
        $file1 = new UploadedFile('E:\wampserver\tmp\php56C1.tmp', 'kitten.jpg.php', 17690, 0);
        $file2 = new UploadedFile('E:\wampserver\tmp\php56D2.tmp', '2MB_FILE.txt', 2097152, 0);
        $file3 = new UploadedFile('E:\wampserver\tmp\php56E2.tmp', 'troll.jpg.php', 15452, 0);
        $file4 = new UploadedFile('E:\wampserver\tmp\php56E3.tmp', 'DVD.ods', 24576, 0);

        $upload = new FileUpload();
        $uploadedFiles = $upload->getUploadedFiles();
        
        $this->assertEquals($file0, $uploadedFiles['fileZero']);
        $this->assertEquals($file1, $uploadedFiles['fileOne']);
        $this->assertEquals($file2, $uploadedFiles['filesArray']['1D']);
        $this->assertEquals($file3, $uploadedFiles['filesArray']['2D1']['2D2']);
        $this->assertEquals($file4, $uploadedFiles['filesArray']['3D1'][0]['3D3']);
    }
}
