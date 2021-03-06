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

use SemelaPavel\String\RegexPattern;
use SemelaPavel\Object\Byte;
use SemelaPavel\Time\LocalDateTime;

/**
 * An instance of this class helps filter out unwanted files by file names using
 * shell wildcards or a regular expression, files with a file size out of set size
 * or range and files with specific date and time or date-time range.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class FileFilter
{
    protected RegexPattern $fileNameWhiteListRegex;
    protected RegexPattern $fileNameBlackListRegex;
    protected RegexPattern $fileNameRegex;
    
    /** @var array<string, int> Pairs of signs of comparison and file sizes in bytes. */
    protected array $sizePredicates = [];
    
    /** @var array<string, \DateTimeInterface> Pairs of signs of comparison and date-times. */
    protected array $timePredicates = [];
    
    /**
     * Sets the whitelist of allowed file names as an array of shell wildcard patterns.
     *  
     * @see \SemelaPavel\String\RegexPattern::fromGlobs()
     * 
     * @param array<string> $globs An array of shell wildcard patterns.
     * @param string $separator Directory structure separator(s).
     * @param bool $caseFold Enables case-insensitive matching if set to true.
     * 
     * @return self This instance for methods chaining.
     */
    public function setFileNameWhiteList(array $globs, string $separator = '/', bool $caseFold = true): self
    {
        $this->fileNameWhiteListRegex = RegexPattern::fromGlobs($globs, $separator, $caseFold);
        
        return $this;
    }
    
    /**
     * Sets the blacklist of file names.
     * 
     * @see \SemelaPavel\String\RegexPattern::fromGlobs()
     * 
     * @param array<string> $globs An array of shell wildcard patterns.
     * @param string $separator Directory structure separator(s).
     * @param bool $caseFold Enables case-insensitive matching if set to true.
     * 
     * @return self This instance for methods chaining.
     */
    public function setFileNameBlackList(array $globs, string $separator = '/', bool $caseFold = true): self
    {
        $this->fileNameBlackListRegex = RegexPattern::fromGlobs($globs, $separator, $caseFold);
                
        return $this;
    }
    
    /**
     * Sets the regular expression to which the file name should match.
     * 
     * @param \SemelaPavel\String\RegexPattern $pattern The regular expression pattern.
     * 
     * @return self This instance for methods chaining.
     */
    public function setFileNameRegex(RegexPattern $pattern): self
    {
        $this->fileNameRegex = $pattern;
                
        return $this;
    }
  
    /**
     * Sets a file size predicate to which the file size should match.
     * Posible predicate operators: >, <, =, >=, <=, <>
     * 
     * Predicate examples:
     * 1024            - the file size MUST match exactly that size in bytes
     * '= 1 KB'        - the file size MUST match exactly that size
     * '1KB'           - the file size MUST match exactly that size
     * '< 1 MB'        - the file size MUST be less than 1 MB
     * '> 1 KB < 1 MB' - the file size MUST be greater than 1 KB and less than 1 MB
     * 
     * Accepts binary units in ISO/IEC 80000 or JEDEC standard.
     * @see Byte::parse()
     * 
     * @param string|int $predicate File size predicate or file size in bytes.
     * 
     * @return self This instance for methods chaining.
     *
     * @throws \InvalidArgumentException If the predicate format cannot be recognized.
     * @throws \SemelaPavel\Object\Exception\ByteParseException If number in predicate
     * string cannot be parsed as a byte.
     * @throws \RangeException If the parsed byte value is out of range.
     */
    public function setFileSize($predicate): self
    {
        if (is_int($predicate)) {
            $this->sizePredicates[''] = $predicate;
        } else {
            $parts = $this->splitPredicate((string) $predicate);
            $count = count($parts);

            for ($i = 1; $i < $count; $i++) {
                $this->sizePredicates[$parts[$i++]] = Byte::parse($parts[$i])->getValue();
            }
        }
      
        return $this;
    }
    
    /**
     * Sets the last modified time of the file as predicate to which
     * the last modified time of the file should match.
     * Posible predicate operators: >, <, =, >=, <=, <>
     * 
     * Predicate examples:
     * new \DateTime('2021-03-01') - the mTime of the file MUST match exactly that date
     * '2021-03-01'                - the mTime of the file MUST match exactly that date
     * '= 2021-03-01'              - the mTime of the file MUST match exactly that date
     * '<> 2021-03-01'             - the mTime of the file MUST NOT match that date
     * '> 2021-01-01 < 2021-03-01' - the mTime of the file MUST be between these two dates
     * 
     * Accepted date and time formats:
     * @link https://www.php.net/manual/en/datetime.formats.php
     * 
     * @param string|int|\DateTimeInterface $predicate Date-time predicate or Unix timestamp.
     * 
     * @return self This instance for methods chaining.
     *
     * @throws \InvalidArgumentException If the predicate format cannot be recognized.
     * @throws \SemelaPavel\Time\Exception\DateTimeParseException If the text cannot
     * be parsed as date-time.
     */
    public function setMTime($predicate): self
    {
        if ($predicate instanceof \DateTimeInterface) {
            $this->timePredicates[''] = $predicate;
        } else {
            $parts = $this->splitPredicate((string) $predicate);
            $count = count($parts);

            for ($i = 1; $i < $count; $i++) {
                $this->timePredicates[$parts[$i++]] = LocalDateTime::parse($parts[$i]);
            }
        }
                
        return $this;
    }

    /**
     * Checks if the given file name matches to the whitelist, blacklist and regular
     * expression.
     * 
     * Returns true only if the file name:
     *     - is in the whitelist, or whitelist is not set
     *     - and is not in blacklist, or blacklist is not set
     *     - and matches the regular expression, or regeular expression is not set
     * 
     * @param string $fileName A file name.
     * 
     * @return bool True if the file name matches the filters, false otherwise.
     * 
     * @throws \SemelaPavel\String\Exception\RegexException If an error occurred
     * while running the expression.
     */
    public function fileNameMatch(string $fileName): bool
    {
        if (isset($this->fileNameWhiteListRegex) && !$this->fileNameWhiteListRegex->match($fileName)) {
               
            return false;
        }
        
        if (isset($this->fileNameBlackListRegex) && $this->fileNameBlackListRegex->match($fileName)) {
   
            return false;
        }
     
        if (isset($this->fileNameRegex) && !$this->fileNameRegex->match($fileName)) {
            
            return false;
        }
        
        return true;
    }

    /**
     * Compares the given file size with the set file size. Returns true
     * if no file size filter is set.
     * 
     * @param int $fileSize File size in bytes.
     * 
     * @return boolean True if the file size match the file size predicate, otherwise false.
     */
    public function compareFileSize(int $fileSize): bool
    {
        foreach ($this->sizePredicates as $operator => $rop) {
            if (!$this->compare($fileSize, $operator, $rop)) {
                
                return false;
            }
        }
        
        return true;
    }

    /**
     * Compares the given date and time with the set date-time. Returns true
     * if no mTime filter is set.
     * 
     * Accepted date and time formats:
     * @link https://www.php.net/manual/en/datetime.formats.php
     * 
     * @param string|int $time Date-time string or Unix timestamp.
     * 
     * @return boolean True if the date-time match date-time predicate, otherwise false.
     */
    public function compareMTime($time): bool
    {
        foreach ($this->timePredicates as $operator => $rop) {
            if (!$this->compare(LocalDateTime::parse((string) $time), $operator, $rop)) {
                
                return false;
            }
        }
        
        return true;
    }

    /**
     * Splits the given predicate to an array of operators and values. Operators
     * and values are sorted in the array in the same order as they are sorted
     * in the predicate string.
     * 
     * e.g. '> 1 KB < 1 MB' = ['>', '1 KB ', '<', '1 MB']
     * 
     * @param string $predicate Predicate containing operator(s) and value(s).
     * 
     * @return array<int, string> All found operators and values.
     * 
     * @throws \InvalidArgumentException If the predicate format cannot be recognized.
     */
    protected function splitPredicate(string $predicate): array
    {
        $p = '\s*([<>=]|[<>]\=?|\=?[<>]|<>)\s*(\d+.*)';
        $pattern = new RegexPattern("(?|(?:^{$p}{$p})|^{$p}|^(\s*)(\d+.*))");
        $matches = [];
        
        if (!\preg_match((string) $pattern, trim($predicate), $matches)) {
            
            throw new \InvalidArgumentException('The predicate format cannot be recognized.');
        }

        return $matches;
    }

    /**
     * Compares the left and right operands based on the inserted operator. 
     * 
     * @param mixed $lop The left operand.
     * @param string $operator String representation of the operator.
     * @param mixed $rop The right operand.
     * 
     * @return bool True if the resulting predicate is true, otherwise false.
     * 
     * @throws \InvalidArgumentException If the operator cannot be recognized.
     */
    protected function compare($lop, string $operator, $rop): bool
    {
        switch ($operator) {
            case '>':
                return $lop > $rop;
                
            case '<':
                return $lop < $rop;
            
            case '':
            case '=':
                return $lop == $rop;
                
            case '>=':
                return $lop >= $rop;
                
            case '<=':
                return $lop <= $rop;
                
            case '<>':
                return $lop != $rop;
        }

        throw new \InvalidArgumentException(sprintf('Unknown operator "%s".', $operator));
    }
}
