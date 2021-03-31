<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Time\Exception;

/**
 * Exception thrown if date time string cannot be parsed as a DateTime object.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class DateTimeParseException extends \RuntimeException
{
    protected $message = 'The string cannot be parsed as a date-time.';
}
