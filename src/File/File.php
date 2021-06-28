<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\File;

use SemelaPavel\File\Exception\{FileNotFoundException, FileException};

/**
 * An instance of this class represents a file in the file system.
 *  
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class File extends \SplFileInfo
{
    /**
     * This should be a safe limit on the length of the file name using
     * UCS-2 characters and the mb_strlen function to check out the length.
     */
    const MAX_FILENAME_LENGTH = 254;
    
    /**
     * Do not use the following reserved names for the name of a file!
     * Also avoid these names followed immediately by an extension; for example,
     * NUL.txt is not recommended. Using these names can lead to problems for
     * Windows users or for a server running PHP on Windows operating system.
     */
    const RESERVED_FILENAMES =
        'CON|PRN|AUX|NUL|COM1|COM2|COM3|COM4|COM5|COM6|COM7|COM8|COM9|LPT1|LPT2|LPT3|LPT4|LPT5|LPT6|LPT7|LPT8|LPT9';
    
    /**
     * List of printable characters reserved by the operating systems,
     * by the URL (RFC 1738) or by the URI (RFC 3986). The list contains
     * also URL unsafe characters (RFC 1738).
     */
    const RESERVED_CHARS = '\/|?*+(){}[]<>!@#$%&=:~`^,;"\'';
    
    /**
     * Creates a new file object from the given file name or full path.
     * The file does not need to exist, or be readable.
     *  
     * @param string $fileName The file name or full path.
     */
    public function __construct(string $fileName)
    {
        parent::__construct($fileName);
    }
    
    /**
     * Reads the file and returns its contents.
     * 
     * @return string File contents.
     * 
     * @throws FileNotFoundException If the file doest not exist.
     * @throws FileException If the file cannot be read.
     */
    public function getContents(): string
    {
        if (!$this->isFile()) {
            throw new FileNotFoundException(sprintf('The file "%s" does not exist.', $this->getPathname()));
        }
        
        $warning = 'no error details are available';
        
        set_error_handler(function ($errno, $errstr) use (&$warning) { $warning = $errstr; return true;});
        $content = \file_get_contents($this->getPathname());
        restore_error_handler();
        
        if ($content === false) {
            throw new FileException(
                sprintf(
                    'The contents of the file "%s" cannot be read: %s',
                    $this->getPathname(),
                    $warning
                )
            );
        }
        
        return $content;
    }
    
    /**
     * Returns the MIME content type for a file as determined
     * by using information from the magic.mime file.
     * 
     * Do not trust the value returned by this method when file comes from
     * a client. A client could send a malicious media type with the intention
     * to corrupt or hack your application. Always check file name extension too.
     * 
     * @return string|null MIME format (e.g. "text/plain") or null on failure.
     */
    public function getMimeType(): ?string
    {
        $mime = @\mime_content_type($this->getPathname());
        if ($mime) {
                
            return $mime;
        }
        
        return null;
    }
   
    /**
     * @see File::isValidFileName()
     * 
     * @return boolean True if the file name is valid, false otherwise.
     */    
    public function hasValidName(): bool
    {
        return static::isValidFileName($this->getFilename());
    }
    
    /**
     * Returns the file name with the ASCII control characters (0-31, 127, 255),
     * whitespaces, slashes, dots and backslashes stripped from the end of string.
     * 
     * @param string $fileName The file name to be modified.
     * 
     * @return string The modified file name.
     */
    public static function rtrimFileName(string $fileName): string
    {
        return rtrim($fileName, "\x00..\x1F \x7F\xFF\\/.");
    }
        
    /**
     * Checks if the file name is safe and valid.
     * See the rules for a valid file name below:
     *  
     * File name length MUST be less than MAX_FILENAME_LENGTH constant.
     * File name MUST NOT be empty.
     * File name MUST NOT be composed only of dots or spaces or a combination of both.
     * File name MUST NOT start with hyphen.
     * File name MUST NOT be a reserved file name (even with added spaces or suffix on the end).
     * File name MUST NOT contains any of the reserved characters.
     * File name MUST NOT contains any of the ASCII control characters (0-31, 127, 255).
     * 
     * @param string $fileName The file name to verify.
     * 
     * @return boolean True if the file name is valid, false otherwise.
     */
    public static function isValidFileName(string $fileName): bool
    {
        if (mb_strlen($fileName) > self::MAX_FILENAME_LENGTH) {
            return false;
        }
        
        $pattern1 = '[' . preg_quote(self::RESERVED_CHARS, "/") . ']';
        $pattern2 = '^[\.\s]*$|^\-.*';
        $pattern3 = '[\x00-\x1F\x7F\xFF]';
        $pattern4 = '^(' . self::RESERVED_FILENAMES . ')\s*(?(?=\.).*)$';
 
        $pattern = "/{$pattern1}|{$pattern2}|{$pattern3}|{$pattern4}/";

        return !((bool) preg_match($pattern, $fileName));
    }
}
