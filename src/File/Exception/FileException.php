<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\File\Exception;

/**
 * Exception thrown when error occurs while working with the file system.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class FileException extends \RuntimeException
{
    /**
     * Construct the exception.
     * 
     * @param string $message The Exception message to throw.
     * @param int $code The Exception code.
     * @param \Throwable $previous The previous exception used for the exception chaining.
     */
    public function __construct(
        string $message,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
