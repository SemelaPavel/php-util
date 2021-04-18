<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Http\Exception;

use SemelaPavel\Http\Exception\FileUploadException;

/**
 * Exception thrown if the file could not be written on disk.
 * 
 * Exception thrown when upload error code UPLOAD_ERR_CANT_WRITE is given.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class FileWriteException extends FileUploadException
{
    /**
     * Construct the exception.
     * 
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param \Throwable $previous The previous exception used for the exception chaining.
     */
    public function __construct(
        string $message = 'The file could not be written on disk.',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
