<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\String;

use SemelaPavel\String\Exception\RegexException;

/**
 * This class represents a regular expression pattern. The resulting pattern
 * can then be used directly in PHP PCRE functions. An object of this class
 * is immutable.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class RegexPattern
{
    /** The flag represents by "i" modifier enables case-insensitive matching. */
    const CASE_INSENSITIVE = 1;
    
    /** The flag represents by "m" modifier enables multiline mode. */
    const MULTILINE = 2;
    
    /** The flag represents by "s" modifier enables dotall mode. */
    const DOTALL = 4;
    
    /** The flag represents by "x" modifier permits whitespace and comments in pattern. */
    const COMMENTS = 8;
    
    /** The flag represents by "u" modifier that treats the pattern and subject strings as UTF-8. */
    const UTF8 = 16;
    
    /** @var string Delimiter used by this class and required by PCRE to enclose the pattern. */
    const DELIMITER = '~';
    
    /**
     * @var array Pairs of PREG error codes and corresponding error messages. 
     */
    protected static $errors = [
        \PREG_INTERNAL_ERROR => 'PCRE internal error occurred.',
        \PREG_BACKTRACK_LIMIT_ERROR => "PCRE's backtracking limit exhausted.",
        \PREG_RECURSION_LIMIT_ERROR => "PCRE's recursion limit exhausted.",
        \PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
        \PREG_BAD_UTF8_OFFSET_ERROR => 'The offset did not correspond to the beginning of a valid UTF-8 code point.'
    ];
    
    protected $regex;
    protected $flags;
    protected $compiledPattern;
    
    /**
     * Returns new RegexPattern object that represents the resulting pattern
     * that can be used directly in PHP PCRE functions.
     * 
     * If you need to safely include a literal string into the pattern, use an array
     * of placeholders in pairs with values to include.
     * 
     * e.g. of binds array: $binds = ['placeholder1' => 'value1']
     * e.g. of regex: '^http[s]?://(placeholder1\.)?domain\.(com|org)$'
     * 
     * Do not use quote function on the values in the binds array, it will be done
     * automatically!
     * 
     * To specify PCRE modifiers use the bit mask of the match flags constants.
     * You can join flags using the bitwise operator |.
     * 
     * e.g. of a bit mask: RegexPattern::CASE_INSENSITIVE | RegexPattern::MULTILINE 
     * 
     * @param string $regex The regular expression (without PCRE delimiter and modifiers).
     * @param int $flags A bit mask of match flags (PCRE modifiers).
     * @param array $binds Values and their binds to a corresponding named placeholders.
     */
    public function __construct($regex, $flags = null, $binds = null)
    {
        
        $this->regex = $this->bindValues($binds, $regex);
        $this->compiledPattern = $this->compilePattern($this->regex, $flags);
        $this->flags = $flags;
    }

    /**
     * Translates glob pattern and its wildcard characters into a regular
     * expression pattern.
     * 
     * The following rules are used to interpret glob patterns:
     *    *      character matches zero or more characters
     *    **     characters matches zero or more characters including the given separator(s)
     *    ?      character matches exactly one character
     *    [abc]  matches one character given in the bracket
     *    [a-z]  matches one character from the range given in the bracket
     *    [!abc] matches one character that is not given in the bracket
     *    [!a-z] matches one character that is not from the range given in the bracket
     * 
     *  - The given separator(s) cannot be matched by a '?' or '*' wildcard,
     *    or by a range like "[.-0]". Only "**" wildcard can match the separator(s).
     *  - The backslash character (\) is used to escape characters that would otherwise
     *    be interpreted as special characters.
     *  - Leading period characters in file name are treated as regular characters.
     * 
     * @param string $pattern The shell wildcard pattern.
     * @param string $separator Directory structure separator(s).
     * @param bool $caseFold Enables case-insensitive matching if set to true.
     * 
     * @return RegexPattern New object that represents the resulting pattern.
     */
    public static function fromGlob($pattern, $separator = '/', $caseFold = true)
    {
        $regexFlags = $caseFold ? self::CASE_INSENSITIVE : null;
        $regex = static::globToRegex($pattern, $separator);
        
        return new static('^' . $regex . '$', $regexFlags, null);
    }
    
    /**
     * Merge the array of glob patterns and translates them into
     * one regular expression pattern.
     * 
     * @see RegexPattern::fromGlob()
     * 
     * @param array $patterns An array of shell wildcard patterns.
     * @param string $separator Directory structure separator(s).
     * @param bool $caseFold Enables case-insensitive matching if set to true.
     * 
     * @return RegexPattern New object that represents the resulting pattern.
     */
    public static function fromGlobs($patterns, $separator = '/', $caseFold = true)
    {
        $regexFlags = $caseFold ? self::CASE_INSENSITIVE : null;
        $regex = '';
        
        foreach ($patterns as $pattern) {
            if ($regex) {
                $regex .= '|';
            }
            $regex .= '(^' . static::globToRegex($pattern, $separator) . '$)';
        }
        
        return new static($regex, $regexFlags, null);
    }

    /**
     * Returns a literal pattern string for the specified string. This method
     * puts a backslash in front of every character that is part of the regular
     * expression syntax and the delimiter character if specified.
     * 
     * The special regex characters that are escaped:
     * . \ + * ? [ ^ ] $ ( ) { } = ! < > | : - #
     * 
     * @param string $str The string to be converted to a literal string.
     * @param string $delimiter If the optional delimiter is specified, it will also be escaped.
     * 
     * @return string A literal string replacement.
     */
    public static function quote($str, $delimiter = null)
    {
        $str = \preg_quote($str, $delimiter);
        
        if (\preg_quote('#') == '#') {
            $str = str_replace('#', '\#', $str);
        }
        
        return $str;
    }
    
    /**
     * Returns a regular expression (with placeholders already replaced
     * by the bound values) from which this pattern was compiled.
     * 
     * @return string The source of this pattern.
     */
    public function getRegex()
    {
        return $this->regex;
    }
    
    /**
     * Returns this pattern's match flags.
     * 
     * @return int The match flags specified when this pattern was compiled.
     */
    public function getFlags()
    {
        return $this->flags;
    }
    
    /**
     * Checks the pattern´s validity.
     * 
     * @return bool True if the pattern is valid, false otherwise.
     */
    public function isValid()
    {
        try {
            $this->match('');
            return true;
            
        } catch (RegexException $e) {
            return false;
        }
    }
    
    /**
     * Performs a regular expression match.
     * 
     * @param string $subject The input string to be matched.
     * 
     * @return bool True if the subject match the pattern, false otherwise.
     * 
     * @throws RegexException If an error occurred while running the expression.
     */
    public function match($subject)
    {
        $warning = '';
        
        set_error_handler(function ($errno, $errstr) use (&$warning) {$warning = $errstr;});
        $result = \preg_match($this->compiledPattern, $subject);
        restore_error_handler();

        if ($result === false) {
            if (isset(static::$errors[\preg_last_error()])) {
                $warning = static::$errors[\preg_last_error()];
            } elseif (strpos($warning, 'preg_match(): ') === 0) {
                $warning = substr($warning, strlen('preg_match(): '));
            } else {
                $warning = 'Compilation failed due to unknown error.';
            }
            
            throw new RegexException($warning, \preg_last_error());
        }
        
        return (bool) $result;
    }

    /**
     * Returns the string representation of regex pattern compiled from
     * regex, binds and the given flags, all surrounded by a PCRE delimiter.
     * 
     * @return string The string representation of this pattern.
     */
    public function __toString()
    {
        return $this->compiledPattern;
    }
    
    /**
     * Translates glob pattern and its wildcard characters into a regular
     * expression pattern. The separator should be a single character, but
     * you can use a sequence of multiple characters as separators.
     * 
     * @param string $glob Glob pattern.
     * @param string $separator Directory structure separator(s).
     * 
     * @return string Glob translated into regex.
     */
    protected static function globToRegex($glob, $separator = '/')
    {
        $slen = strlen($separator);
        $exclude = ']';
        
        for ($i = 0; $i < $slen; $i++) {
            $exclude .= '(?<!' . static::quote($separator[$i]) . ')';
        }
        
        $separator = static::quote($separator);
        
        $tr = [
            '\*\*' => '.*',
            '\*' => $separator ? '[^' . $separator . ']*' : '.*',
            '\?' => $separator ? '[^' . $separator . ']' : '.',
            '\[\!' => '[^',
            '\[' => '[',
            '\-' => '-',
            '\]' => $exclude,
            '\\\\\\' => '\\'
        ];
        return strtr(static::quote($glob), $tr);
    }
    
    /**
     * Returns a regular expression with placeholders replaced by the values
     * from the given binds array. Values are quoted by the quote function
     * without a delimiter specified - the delimiter should be escaped later
     * in compile method.
     * 
     * @param array $binds Values and their binds to a corresponding named placeholders.
     * @param string $regex The regular expression (without PCRE delimiter and modifiers).
     * 
     * @return string
     */
    protected function bindValues($binds, $regex)
    {
        if ($binds == null) {
            $binds = [];
        }
                
        $tr = [];
                
        foreach ($binds as $key => $value) {
            $tr[$key] = static::quote($value);
        }
                
        return strtr($regex, $tr);
    }
    
    /**
     * Converts the given flags into a string containing PCRE modifiers.
     * 
     * @param int $flags Match flags, a bit mask of PCRE modifiers.
     * 
     * @return string PCRE regex modifiers set.
     */
    protected function getModifiersFromFlags($flags)
    {
        $modifiers = '';
        
        if ($flags & self::CASE_INSENSITIVE) {
            $modifiers .= 'i';
        }
        if ($flags & self::MULTILINE) {
            $modifiers .= 'm';
        }
        if ($flags & self::DOTALL) {
            $modifiers .= 's';
        }
        if ($flags & self::COMMENTS) {
            $modifiers .= 'x';
        }
        if ($flags & self::UTF8) {
            $modifiers .= 'u';
        }
        
        return $modifiers;
    }
    
    /**
     * Returns prepared regex pattern compiled from a regex, delimiter and given flags.
     * 
     * @param string $regex The regular expression (without PCRE delimiter and modifiers).
     * @param int $flags A bit mask of match flags (PCRE modifiers).
     * 
     * @return string A complete pattern from the regex, delimiter and the given flags.
     */
    protected function compilePattern($regex, $flags)
    {
        $compiledPattern = self::DELIMITER;
        $compiledPattern .= str_replace(self::DELIMITER, '\\' . self::DELIMITER, $regex);
        $compiledPattern .= self::DELIMITER;

       if ($flags !== null) {
            $compiledPattern .= static::getModifiersFromFlags($flags);
        }
        
        return $compiledPattern;
    }
}
