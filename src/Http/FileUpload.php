<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Http;

use SemelaPavel\Object\Byte;
use SemelaPavel\Http\UploadedFile;

/**
 * The class provides basic functionality for retrieving normalized file upload
 * data for further processing. The class can handle unlimited single and multiple
 * files upload.
 *   
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class FileUpload
{
    /**
     * @var array All uploaded files, including empty HTML inputs.
     */
    protected array $files = [];
    
    /**
     * Creates instance of FileUpload class and initializes this instance from
     * global array variable $_FILES.
     * 
     * Be sure your file upload form has attribute enctype="multipart/form-data"
     * otherwise the files upload will not work.
     * 
     * @throws \RuntimeException If file uploads not allowed in php.ini.
     */
    public function __construct()
    {
        if (!(bool) ini_get('file_uploads')) {
            throw new \RuntimeException('File uploads not allowed.');
        }
        
        $this->files = $this->processUploadedFiles($_FILES);
    }
    
    /**
     * Returns upload metadata in a normalized tree, with each leaf
     * an instance of UploadedFile or null if error UPLOAD_ERR_NO_FILE occured.
     * 
     * See the example of HTML form input and normalized tree below:
     * 
     * input name="oneFile" is returned as $files['oneFile']
     * input name="filesArray[]" is returned as $files['filesArray'][0]
     * input name="filesArray[file1][]" is returned as $files['filesArray']['file1][0]
     * etc.
     * 
     * @return array An array tree of UploadedFile instances or null values.
     * 
     * @throws \LengthException If upload exceeds the post_max_size directive in php.ini.
     */
    public function getUploadedFiles(): array
    {
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            if ($_SERVER['CONTENT_LENGTH'] > static::maxPostSize()) {
                throw new \LengthException('Upload exceeds the post_max_size directive in php.ini.');
            }
        }
        
        return $this->files;
    }
        
    /**
     * Returns the maximum number of files allowed to be uploaded simultaneously.
     * Keep in mind that sum of the sizes of all uploaded files must be less
     * than the maximum size of post data.
     * 
     * Read only directive (PHP_INI_SYSTEM).
     * 
     * @return int The number of files allowed to be uploaded simultaneously.
     */
    public static function maxFileUploads(): int
    {
        return (int) ini_get('max_file_uploads');
    }
    
    /**
     * Returns the maximum size of post data in bytes. This value also affects
     * file upload. The limit must be greater than the sum of the sizes
     * of all uploaded files, because the size of the other form data is also
     * taken into account.
     * 
     * Read only directive (PHP_INI_PERDIR).
     * 
     * @return int The maximum size of post data in bytes.
     */
    public static function maxPostSize(): int
    {
        $iniPostLimit = ini_get('post_max_size');
        
        if ($iniPostLimit == 0) {
            $postLimit = new Byte(Byte::MAX_VALUE);
        } else {
            $postLimit = Byte::fromPhpIniNotation($iniPostLimit);
        }
        
        return $postLimit->getValue();
    }
    
    /**
     * Returns the maximum size in bytes of an uploaded file. Keep in mind that
     * file size limit must be less than the maximum size of post data.
     * Combines "post_max_size" and "upload_max_filesize" directives to get
     * real max file size for upload.
     * 
     * Read only directive (PHP_INI_PERDIR).
     * 
     * @return int The maximum size in bytes of an uploaded file.
     */
    public static function maxUploadFileSize(): int
    {
        $fileSizeLimit = Byte::fromPhpIniNotation(ini_get('upload_max_filesize'));

        return min(
            $fileSizeLimit->getValue(), 
            static::maxPostSize()
        );
    }
    
    /**
     * Fills the internal files array with all uploaded files from global
     * array $_FILES. The uploaded files are stored as UploadedFile instances.
     * If there is no file metadata from any HTML input, a null value is stored.
     *    
     * @param array $uploadedFiles Array with files metadata to normalize.
     * 
     * @return array An array tree of UploadedFile instances or null values.
     */
    protected function processUploadedFiles(array $uploadedFiles): array
    {
        $files = [];
        
        foreach ($uploadedFiles as $inputName => $uploadedFile) {
            if (is_array($uploadedFile['error'])) {
                $subArray = [];
                foreach ($uploadedFile['error'] as $key => $value) {
                    $subArray[$key]['name'] = $uploadedFile['name'][$key];
                    $subArray[$key]['type'] = $uploadedFile['type'][$key];
                    $subArray[$key]['tmp_name'] = $uploadedFile['tmp_name'][$key];
                    $subArray[$key]['error'] = $uploadedFile['error'][$key];
                    $subArray[$key]['size'] = $uploadedFile['size'][$key];

                    $files[$inputName] = $this->processUploadedFiles($subArray);
                }
            } else {
                $files[$inputName] = $this->processFile($uploadedFile);
            }
        }
        
        return $files;
    }
    
    /**
     * Parses uploaded file metadata from given array and returns prepared
     * instance of UploadedFile or null if the metadata contains
     * and UPLOAD_ERR_NO_FILE error code.
     * 
     * @param array $uploadedFile Uploaded file metadata array.
     * 
     * @return UploadedFile|null Uploaded file metadata as UploadedFile or null.
     */
    protected function processFile(array $uploadedFile): ?UploadedFile
    {
        if ($uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = new UploadedFile(
                    $uploadedFile['tmp_name'],
                    $uploadedFile['name'],
                    $uploadedFile['size'],
                    $uploadedFile['error']
            );
        } else {
            $file = null;
        }

        return $file;
    }
}
