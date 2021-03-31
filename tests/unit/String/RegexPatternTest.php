<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use SemelaPavel\String\RegexPattern;
use SemelaPavel\String\Exception\RegexException;

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\String\RegexPattern
 * @uses \SemelaPavel\String\Exception\RegexException
 */
final class RegexPatternTest extends TestCase
{
    /**
     * Prepared regex pattern for the test bellow. This test does not perform
     * any assertions itself, but must not cause any errors or outputs.
     * 
     * @doesNotPerformAssertions
     */
    public function testFromGlobs()
    {
        $globs = ['[.-0a-z].jpg', '[!0-9]???.PNG', '*.gif', '**.txt', 'file.[txc]??'];

        return [
            0 => [RegexPattern::fromGlobs($globs)],
            1 => [RegexPattern::fromGlobs($globs, '\\/')],
            2 => [RegexPattern::fromGlobs($globs, '\\/', false)]
        ];
    }

    /**
     * Data provider for testFromGlobsMatch().
     * [index of RegexPattern object from testFromGlobs(), subject to match, result]
     */
    public function globsMatchProvider()
    {
        return [
            [0, 'a.JPG', true],
            [0, 'B.jpg', true],
            [0, '0.jpg', true],
            [0, '/a.jpg', false],
            [0, 'ab.jpg', false],
            [1, '\\a.jpg', false],
            [2, 'a.JPG', false],
            
            [0, 'a123.png', true],
            [0, 'a1\\3.png', true],
            [0, '\\123.PNG', true],
            [0, '0123.PNG', false],
            [0, '/123.PNG', false],
            [0, 'a1/3.PNG', false],
            [1, 'a1\\3.PNG', false],
            [1, '\\123.PNG', false],
                        
            [0, 'image.gif', true],
            [0, 'im\\age.gif', true],
            [0, 'im//age.gif', false],
            [1, 'im\\age.gif', false],
            
            [0, 'text/file.txt', true],
            [0, 'text\\file.txt', true],
            [1, 'text/file.txt', true],
            [1, 'text\\file.txt', true],
            
            [0, 'file.txt', true],
            [0, 'file.xml', true],
            [0, 'file.csv', true]
        ];
    }

    /**
     * Prepared regex pattern for the test bellow. This test does not perform
     * any assertions itself, but must not cause any errors or outputs.
     * 
     * @doesNotPerformAssertions
     */
    public function testFromGlob()
    {
        $glob = '/home/**[P][!a-t]*/*[.-9]*?.???';

        return [
            0 => [RegexPattern::fromGlob($glob)],
            1 => [RegexPattern::fromGlob($glob, '\\/')],
            2 => [RegexPattern::fromGlob($glob, '\\/', false)]
        ];
    }

    /**
     * Data provider for testFromGlobMatch().
     * [index of RegexPattern object from testFromGlobs(), subject to match, result]
     */
    public function globMatchProvider()
    {
        return [
            [0, '/home/Pu/1a.t\\t', true],
            [0, '/home/dir\\Pu/\\1a.txt', true],
            [0, '/HOME/Public/file1a.txt', true],
            [0, '/home/user/pavel/public/file1a.txt', true],
            [0, '/home/Pa/1a.txt', false],
            [0, '/home/Public/a.txt', false],
            [0, '/home/Public/dir/a.txt', false],
            [0, '/home/user/pavel/public/file1a.c', false],
            [1, '/home/dir\\Pu/1a.txt', true],
            [1, '/home/Pu/1a.t\\t', false],
            [1, '/home/Pu/\\1a.txt', false],
            [2, '/home/user/pavel/public/file1a.txt', false],
        ];
    }

    /**
     * @dataProvider globMatchProvider
     * @depends testFromGlob
     */
    public function testFromGlobMatch($i, $subject, $matchResult, $regexes)
    {
        $this->assertSame($matchResult, $regexes[$i][0]->match($subject));
    }

    /**
     * @dataProvider globsMatchProvider
     * @depends testFromGlobs
     */
    public function testFromGlobsMatch($i, $subject, $matchResult, $regexes)
    {
        $this->assertSame($matchResult, $regexes[$i][0]->match($subject));
    }

    public function testQuote()
    {
        $toQuote = '.+*?[^]$(){}=!<>|:-#\\' . '~';
        $quoted = '\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:\-\#\\\\';

        $this->assertSame($quoted . '~', RegexPattern::quote($toQuote));
        $this->assertSame($quoted . '\~', RegexPattern::quote($toQuote, '~'));
    }

    /**
     * Prepared regex pattern for the tests bellow. This test does not perform
     * any assertions itself, but must not cause any errors or outputs.
     * 
     * @doesNotPerformAssertions
     */
    public function testConstructProvider()
    {
        return [
            0 => [new RegexPattern('^[a-z]\:\\\\DIR~1\\\\file\.txt$')],
            1 => [new RegexPattern('file\.(jpg|png)', RegexPattern::CASE_INSENSITIVE)],
            2 => [new RegexPattern("^line[0-9]$\n^line[0-9]$", RegexPattern::MULTILINE)],
            3 => [new RegexPattern('^line1.*line2$', RegexPattern::DOTALL)],
            4 => [new RegexPattern('.* # match all', RegexPattern::COMMENTS)],
            5 => [new RegexPattern("\xc3\xb1", RegexPattern::UTF8)],
            6 => [new RegexPattern(
                    "UTF8\xc3\xb1.*$ #UTF8 regex",
                    RegexPattern::CASE_INSENSITIVE
                    | RegexPattern::MULTILINE
                    | RegexPattern::DOTALL
                    | RegexPattern::COMMENTS
                    | RegexPattern::UTF8
                )],
            7 => [new RegexPattern(
                    '^Binds~@bind1\\\\bind2$',
                    RegexPattern::CASE_INSENSITIVE | RegexPattern::COMMENTS,
                    ['@bind1' => '~value1', 'bind2' => '#value2']
                )],
            8 => [new RegexPattern(
                    '^Binds~@bind1\\\\bind2$',
                    RegexPattern::CASE_INSENSITIVE,
                    ['@bind1' => '~value1', 'bind2' => '#value2']
                )]
        ];
    }

    /**
     * Data provider for testGetRegex().
     * [row name, index of RegexPattern object from testConstructProvider(), result]
     */
    public function getRegexProvider()
    {
        return [
            'Basic regex pattern' => [0, '^[a-z]\:\\\\DIR~1\\\\file\.txt$'],
            'CASE_I. regex pattern' => [1, 'file\.(jpg|png)'],
            'MULTILINE regex pattern' => [2, "^line[0-9]$\n^line[0-9]$"],
            'DOTALL regex pattern' => [3, '^line1.*line2$'],
            'COMMENTS regex pattern' => [4, '.* # match all'],
            'UTF8 regex pattern' => [5, "\xc3\xb1"],
            'All flags regex pattern' => [6, "UTF8\xc3\xb1.*$ #UTF8 regex"],
            'Regex w/ binds pattern' => [7, '^Binds~~value1\\\\\#value2$'],
            'CASE_I. regex w/ binds pattern' => [8, '^Binds~~value1\\\\\#value2$']
        ];
    }

    /**
     * Data provider for testGetRegex().
     * [row name, index of RegexPattern object from testConstructProvider(), result]
     */
    public function getFlagsProvider()
    {
        return [
            'Basic regex flag' => [0, 0],
            'CASE_I. regex flag' => [1, RegexPattern::CASE_INSENSITIVE],
            'MULTILINE regex flag' => [2, RegexPattern::MULTILINE],
            'DOTALL regex flag' => [3, RegexPattern::DOTALL],
            'COMMENTS regex flag' => [4, RegexPattern::COMMENTS],
            'UTF8 regex flag' => [5, RegexPattern::UTF8],
            'All flags regex flag' => [6,
                RegexPattern::CASE_INSENSITIVE
                | RegexPattern::MULTILINE
                | RegexPattern::DOTALL
                | RegexPattern::COMMENTS
                | RegexPattern::UTF8
            ],
            'Regex w/ binds flag' => [7,
                RegexPattern::CASE_INSENSITIVE |
                RegexPattern::COMMENTS
            ],
            'CASE_I. regex w/ binds flag' => [8, RegexPattern::CASE_INSENSITIVE]
        ];
    }

    /**
     * Data provider for testMatch().
     * [row name, index of RegexPattern object from testConstructProvider(), result]
     */
    public function regexPatternMatchProvider()
    {
        return [
            'Basic regex match 1' => [0, 'c:\\DIR~1\\file.txt', true],
            'Basic regex match 0' => [0, 'C:\\DIR~1\\file.txt', false],
            'CASE_I. regex match 1' => [1, 'FILE.PNG', true],
            'MULTILINE regex match 1' => [2, "line1\nline2", true],
            'DOTALL regex match 1' => [3, "line1\nline2", true],
            'COMMENTS regex match 1' => [4, 'subject', true],
            'UTF8 regex match 1' => [5, "\xc3\xb1", true],
            'All flags regex match 1' => [6, "utf8\xc3\xb1\nline2", true],
            'Regex w/ binds match 1' => [7, 'Binds~~value1\\#value2', true],
            'CASE_I. regex w/ binds match 1' => [8, 'binds~~value1\\#value2', true]
        ];
    }

    /**
     * Data provider for testToString().
     * [row name, index of RegexPattern object from testConstructProvider(), result]
     */
    public function regexPatternStringProvider()
    {
        return [
            'Basic regex to string' => [0, '~^[a-z]\:\\\\DIR\~1\\\\file\.txt$~'],
            'CASE_I. regex to string' => [1, '~file\.(jpg|png)~i'],
            'MULTILINE regex to string' => [2, "~^line[0-9]$\n^line[0-9]$~m"],
            'DOTALL regex to string' => [3, '~^line1.*line2$~s'],
            'COMMENTS regex to string' => [4, '~.* # match all~x'],
            'UTF8 regex to string' => [5, "~\xc3\xb1~u"],
            'All flags regex to string' => [6, "~UTF8\xc3\xb1.*$ #UTF8 regex~imsxu"],
            'Regex w/ binds to string' => [7, '~^Binds\~\~value1\\\\\#value2$~ix'],
            'CASE_I. regex w/ binds to string' => [8, '~^Binds\~\~value1\\\\\#value2$~i']
        ];
    }

    /**
     * [row name, RegexPattern, subject to match, exception message regex]
     */
    public function regexExceptionProvider()
    {
        return [
            'Unclosed [' => [
                new RegexPattern('['),
                '[',
                '/.*/'
            ],
            'Malformed UTF8' => [
                new RegexPattern("foo", RegexPattern::UTF8),
                "\xf8\xa1\xa1\xa1\xa1",
                '/Malformed.*/'
            ],
            'Malformed regex UTF8' => [
                new RegexPattern("\xf8\xa1\xa1\xa1\xa1", RegexPattern::UTF8),
                "foo",
                '/Malformed.*/'
            ]
        ];
    }

    /**
     * @dataProvider getRegexProvider
     * @depends testConstructProvider
     */
    public function testGetRegex($i, $patternString, $regexes)
    {
        $this->assertSame($patternString, $regexes[$i][0]->getRegex());
    }

    /**
     * @dataProvider getFlagsProvider
     * @depends testConstructProvider
     */
    public function testGetFlags($i, $flags, $regexes)
    {
        $this->assertSame($flags, $regexes[$i][0]->getFlags());
    }

    /**
     * @dataProvider testConstructProvider
     */
    public function testIsValidValidPatterns($regex)
    {
        $this->assertTrue($regex->isValid());
    }

    public function testIsValidInvalidPatterns()
    {
        $this->assertFalse((new RegexPattern('['))->isValid());
        $this->assertFalse((new RegexPattern("\xf8\xa1\xa1\xa1\xa1", RegexPattern::UTF8))->isValid());
    }

    /**
     * @dataProvider regexPatternMatchProvider
     * @depends testConstructProvider
     */
    public function testMatch($i, $subject, $matchResult, $regexes)
    {
        $this->assertSame($matchResult, $regexes[$i][0]->match($subject));
    }

    /**
     * @dataProvider regexExceptionProvider
     */
    public function testMatchRegexException($regex, $subject, $exceptionMsgRegex)
    {
        $this->expectException(RegexException::class);
        $regex->match($subject);
        $this->expectExceptionMessageMatches($exceptionMsgRegex);
    }

    /**
     * @dataProvider regexPatternStringProvider
     * @depends testConstructProvider
     */
    public function testToString($i, $regexString, $regexes)
    {
        $this->assertSame($regexString, (string) $regexes[$i][0]);
    }
}
