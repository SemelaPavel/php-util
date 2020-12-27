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
 * Exception thrown if some PHP extension stopped the file upload. PHP does not
 * provide a way to ascertain which extension caused the file upload to stop;
 * examining the list of loaded extensions with phpinfo() may help.
 * 
 * Exception thrown when upload error code UPLOAD_ERR_EXTENSION is given.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class UploadStoppedException extends FileUploadException
{
    protected $message = 'A PHP extension stopped the file upload.';
}
