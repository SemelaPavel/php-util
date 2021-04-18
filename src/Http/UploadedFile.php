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

use SemelaPavel\Http\Exception\{
    FileUploadException,
    IniFileSizeException,
    FormFileSizeException,
    PartialFileException,
    NoFileUploadedException,
    NoTmpDirException,
    FileWriteException,
    UploadStoppedException
};

use SemelaPavel\File\File;
use SemelaPavel\File\Exception\{FileException, InvalidFileNameException};

/**
 * An instance of this class represents a file uploaded via an HTTP request.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class UploadedFile extends File
{
    /**
     * @var array<int, string> Pairs of upload error codes and corresponding error messages. 
     */
    protected static array $errors = [
        \UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds the upload_max_filesize ini directive.',
        \UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the MAX_FILE_SIZE directive in HTML form.',
        \UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
        \UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder for uploaded files.',
        \UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
        \UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload of the "%s" file.'
    ];

    protected string $clientFileName;
    protected int $size;
    protected int $error;
    
    /**
     * Creates an instance representing a file uploaded via an HTTP request.
     * 
     * @param string $tmpPathName The full temporary path to the uploaded file.
     * @param string $clientFileName The filename sent by the client.
     * @param int $size The file size in bytes.
     * @param int $error The UPLOAD_ERR_XXX code.
     */
    public function __construct(string $tmpPathName, string $clientFileName, int $size, int $error)
    {
        $this->clientFileName = $clientFileName;
        $this->size = $size;
        $this->error = $error;
        
        parent::__construct($tmpPathName);
    }
    
    /**
     * Returns the filename sent by the client.
     * 
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     * 
     * @return string The filename sent by the client.
     */
    public function getClientFilename(): string
    {
        return $this->clientFileName;
    }
    
    /**
     * Returns the file size.
     * 
     * @return int The file size in bytes.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the error code associated with the uploaded file. If the upload
     * was successful, the constant UPLOAD_ERR_OK is returned. Otherwise one
     * of the other UPLOAD_ERR_XXX constants is returned.
     * 
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int
    {
        return $this->error;
    }
    
    /**
     * Returns whether the file was uploaded successfully via an HTTP request
     * and no error occurred.
     * 
     * @return bool True if the file was uploaded successfully.
     */
    public function isUploaded(): bool
    {
        return \UPLOAD_ERR_OK === $this->error && $this->isUploadedFile();
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasValidName(): bool
    {
        return static::isValidFileName($this->getClientFilename());
    }

    /**
     * Moves the uploaded file to a new location. If the target location doesn't
     * exist, it will be created first. The existing file will be overwritten.
     * File access permissions will be changed to 0644 in the new location.
     * 
     * @param string $targetPath Path to which to move the uploaded file.
     * @param string $newFileName New file name.
     * 
     * @return File New File object created from a moved file.
     * 
     * @throws IniFileSizeException If the file exceeds the upload_max_filesize.
     * @throws FormFileSizeException If the file exceeds the MAX_FILE_SIZE.
     * @throws PartialFileException If the file was only partially uploaded.
     * @throws NoFileUploadedException If no file was uploaded.
     * @throws NoTmpDirException If a temporary folder is missing.
     * @throws FileWriteException If the file could not be written on disk.
     * @throws UploadStoppedException If a PHP extension stopped the upload.
     * @throws FileUploadException If an unknown upload error occurs or the file cannot be moved.
     * @throws InvalidFileNameException If the file name is not valid or safe.
     * @throws FileException If unable to write or create target directory.
     */
    public function move(string $targetPath, string $newFileName = null): File
    {
        if (!$this->isUploaded()) {
            $this->throwUploadErrException();
        }
                
        $fileName = $newFileName !== null ? $newFileName : $this->clientFileName;
        
        if (!static::isValidFileName($fileName)) {
            throw new InvalidFileNameException(sprintf('The file name "%s" is not valid or safe.', $fileName));
        }

        $targetPathName = $this->prepareTargetDirectory($targetPath);
        $targetPathName .= DIRECTORY_SEPARATOR;
        $targetPathName .= $fileName;

        $warning = 'The file is not a valid uploaded file.';

        set_error_handler(function ($errno, $errstr) use (&$warning) { $warning = $errstr; return true;});
        $isMoved = $this->moveUploadedFile($this->getPathname(), $targetPathName);
        restore_error_handler();

        if (!$isMoved) {
            throw new FileUploadException(
                sprintf(
                    'The file "%s" could not be moved to "%s": %s',
                    $this->clientFileName,
                    $targetPathName,
                    $warning
                )
            );
        }

        @chmod($targetPathName, 0644);

        return new File($targetPathName);
    }

    /**
     * Prepares the directory or directories for uploading the file. Returns
     * a path without any trailing directory separators, control characters and
     * spaces, dots or slashes on the end of the path, on success.
     * 
     * @param string $targetPath Path to the directory for uploading the file.
     * 
     * @return string Prepared directory path.
     * 
     * @throws FileException If the directory could not be created or written to.
     */
    protected function prepareTargetDirectory(string $targetPath): string
    {
        $targetPath = static::rtrimFileName($targetPath);
        
        if (!is_dir($targetPath) && @mkdir($targetPath, 0777, true) == false) {
            throw new FileException(sprintf('Failed to create directory "%s".', $targetPath));
        }
        
        if (!is_writable($targetPath)) {
            throw new FileException(sprintf('Unable to write to directory "%s".', $targetPath));
        }
        
        return $targetPath;
    }
    
    /**
     * Wrapper of is_uploaded_file function. Checks whether the file was uploaded
     * via HTTP POST or not.
     * 
     * @return bool True if the file was uploaded via HTTP POST, false otherwise.
     */
    protected function isUploadedFile(): bool
    {
        return \is_uploaded_file($this->getPathname());
    }
    
    /**
     * Wrapper of move_uploaded_file function. Moves an uploaded file to a new location.
     * If filename is a valid uploaded file, but cannot be moved for some reason,
     * no action will occur, and additionally, warning will be issued.
     * 
     * @param string $sourcePathName The path to the temporary file uploaded via HTTP POST.
     * @param string $targetPathName The path with the file name to which to move the uploaded file.
     * 
     * @return bool True on success, false if the source file is not valid, or cannot be moved.
     */
    protected function moveUploadedFile(string $sourcePathName, string $targetPathName): bool
    {
        return \move_uploaded_file($sourcePathName, $targetPathName);
    }

    /**
     * Throws specific exception for the uploaded file and its upload error code.
     * Exceptions are all iherited from FileUploadException class, or FileUploadException
     * instance can be thrown itself for uknown error code.
     * 
     * @throws FileUploadException Upload error specific exception.
     */
    protected function throwUploadErrException(): void
    {
        switch ($this->error) {
            case \UPLOAD_ERR_OK:
                break;

            case \UPLOAD_ERR_INI_SIZE:
                throw new IniFileSizeException($this->getUploadErrMessage(), $this->error);
                
            case \UPLOAD_ERR_FORM_SIZE:
                throw new FormFileSizeException($this->getUploadErrMessage(), $this->error);
                
            case \UPLOAD_ERR_PARTIAL:
                throw new PartialFileException($this->getUploadErrMessage(), $this->error);
                
            case \UPLOAD_ERR_NO_FILE:
                throw new NoFileUploadedException($this->getUploadErrMessage(), $this->error);
                
            case \UPLOAD_ERR_NO_TMP_DIR:
                throw new NoTmpDirException($this->getUploadErrMessage(), $this->error);
                
            case \UPLOAD_ERR_CANT_WRITE:
                throw new FileWriteException($this->getUploadErrMessage(), $this->error);

            case \UPLOAD_ERR_EXTENSION:
                throw new UploadStoppedException($this->getUploadErrMessage(), $this->error);
                
            default:
                throw new FileUploadException($this->getUploadErrMessage(), $this->error);
        }
    }
    
    /**
     * Returns formatted error message specific to the file and its upload
     * error code. There is no message for UPLOAD_ERR_NO_FILE error code.
     * 
     * @return string Formatted error message corresponding to the upload error code.
     */
    protected function getUploadErrMessage(): string
    {
        if (isset(static::$errors[$this->error])) {
            $message = sprintf(static::$errors[$this->error], $this->clientFileName);
        } else {
            $message = sprintf(
                'An unknown error interupted the upload of the "%s" file.',
                $this->clientFileName
            );
        }
        
        return $message;
    }
}
