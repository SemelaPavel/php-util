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
 * Exception thrown if no file was uploaded.
 * 
 * Exception thrown when upload error code UPLOAD_ERR_NO_FILE is given.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class NoFileUploadedException extends FileUploadException
{
    protected $message = 'No file was uploaded.';
}
