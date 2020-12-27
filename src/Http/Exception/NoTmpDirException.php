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
 * Exception thrown if a temporary folder is missing.
 * 
 * Exception thrown when upload error code UPLOAD_ERR_NO_TMP_DIR is given.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class NoTmpDirException extends FileUploadException
{
    protected $message = 'Missing a temporary folder.';
}
