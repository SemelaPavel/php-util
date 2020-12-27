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
 * Exception thrown if the uploaded file exceeds the MAX_FILE_SIZE directive
 * that was specified in the HTML form.
 * 
 * Exception thrown when upload error code UPLOAD_ERR_FORM_SIZE is given.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class FormFileSizeException extends FileUploadException
{
    protected $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive in HTML form.';
}
