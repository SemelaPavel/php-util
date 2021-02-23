<?php
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
 */
final class RegexPatternTest extends TestCase
{
    public function testFromGlob()
    {
        $regex = RegexPattern::fromGlob('/home/**[P][!a-t]*/*[.-9]*?.???', '\\/', true);
        
        $this->assertTrue($regex->match('/home/public/.a.txt'));
        $this->assertTrue($regex->match('/home/user/public/.file.txt'));
        $this->assertTrue($regex->match('/home/user\\docs\\Public/the1file.txt'));
        
        $this->assertFalse($regex->match('/home/pablic/.a.txt'));
        $this->assertFalse($regex->match('/home/public/1.txt'));
        $this->assertFalse($regex->match('/home/public/.htaccess'));
        $this->assertFalse($regex->match('/home/user/public/foo/.file.txt'));
        $this->assertFalse($regex->match('/home/user/docs/Public/the1file/.txt'));
        $this->assertFalse($regex->match(' /home/user/public/.file.txt'));
        $this->assertFalse($regex->match('/home/user/public/.file.txt '));
    }
    
    public function testFromGlobs()
    {
        $globs = ['[a-z].jpg', '[!0-9]???.PNG', '*.gif', '**.txt', 'file.???'];
        
        $regex1 = RegexPattern::fromGlobs($globs);
        
        $this->assertTrue($regex1->match('a.jpg'));
        $this->assertTrue($regex1->match('a123.png'));
        $this->assertTrue($regex1->match('image.gif'));
        $this->assertTrue($regex1->match('\\home/file.txt'));
        $this->assertTrue($regex1->match('file.img'));
        
        $this->assertFalse($regex1->match('1.jpg'));
        $this->assertFalse($regex1->match('foo.PNG '));
        $this->assertFalse($regex1->match('2foo.PNG '));
        $this->assertFalse($regex1->match('home/image.gif'));
        $this->assertFalse($regex1->match('image.gif '));
        $this->assertFalse($regex1->match('image.php'));
        $this->assertFalse($regex1->match(' file.img'));
        
        $regex2 = RegexPattern::fromGlobs($globs, '\\/', false);
        
        $this->assertTrue($regex2->match('\\home/file.txt'));
        
        $this->assertFalse($regex2->match('a123.png'));
        $this->assertFalse($regex2->match('home\\image.gif'));
        $this->assertFalse($regex2->match('home/image.gif'));
    }
    
    public function testQuote()
    {
        $toQuote = '.+*?[^]$(){}=!<>|:-#\\' . '~';
        $quoted = '\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:\-\#\\\\';
        
        $this->assertSame($quoted . '~', RegexPattern::quote($toQuote));
        $this->assertSame($quoted . '\~', RegexPattern::quote($toQuote, '~'));
    }
    
    public function testGetRegex()
    {
        $regex = '^[T]est~@bind1\\\\bind2$';
        $flags = RegexPattern::CASE_INSENSITIVE
            | RegexPattern::MULTILINE
            | RegexPattern::DOTALL
            | RegexPattern::COMMENTS
            | RegexPattern::UTF8;
        $binds = ['@bind1' => '~value1', 'bind2' => '#value2'];
        
        $pattern1 = new RegexPattern($regex);
        $pattern2 = new RegexPattern($regex, $flags);
        $pattern3 = new RegexPattern($regex, $flags, $binds);
        
        $exp1 = '^[T]est~@bind1\\\\bind2$';
        $exp2 = '^[T]est~@bind1\\\\bind2$';
        $exp3 = '^[T]est~~value1\\\\\#value2$';
        
        $this->assertSame($exp1, $pattern1->getRegex());
        $this->assertSame($exp2, $pattern2->getRegex());
        $this->assertSame($exp3, $pattern3->getRegex());
    }
    
    public function testGetFlags()
    {
        $flag = RegexPattern::CASE_INSENSITIVE;
        $flags = $flag;
        $this->assertSame($flag, (new RegexPattern('regex', $flag))->getFlags());
        $this->assertSame($flags, (new RegexPattern('regex', $flags))->getFlags());
        
        $flag = RegexPattern::MULTILINE;
        $flags |= $flag;
        $this->assertSame($flag, (new RegexPattern('regex', $flag))->getFlags());
        $this->assertSame($flags, (new RegexPattern('regex', $flags))->getFlags());
        
        $flag = RegexPattern::DOTALL;
        $flags |= $flag;
        $this->assertSame($flag, (new RegexPattern('regex', $flag))->getFlags());
        $this->assertSame($flags, (new RegexPattern('regex', $flags))->getFlags());
        
        $flag = RegexPattern::COMMENTS;
        $flags |= $flag;
        $this->assertSame($flag, (new RegexPattern('regex', $flag))->getFlags());
        $this->assertSame($flags, (new RegexPattern('regex', $flags))->getFlags());
        
        $flag = RegexPattern::UTF8;
        $flags |= $flag;
        $this->assertSame($flag, (new RegexPattern('regex', $flag))->getFlags());
        $this->assertSame($flags, (new RegexPattern('regex', $flags))->getFlags());
    }
    
    public function testIsValid()
    {
        $this->assertTrue((new RegexPattern('foo~bar'))->isValid());
        $this->assertTrue((new RegexPattern('foo~bar', RegexPattern::CASE_INSENSITIVE))->isValid());
        
        $this->assertFalse((new RegexPattern('[', RegexPattern::UTF8))->isValid());
        $this->assertFalse((new RegexPattern("\xf8\xa1\xa1\xa1\xa1", RegexPattern::UTF8))->isValid());
                
        $regex = '^[T]est~@bind1\\\\bind2$';
        $flags = RegexPattern::CASE_INSENSITIVE
            | RegexPattern::MULTILINE
            | RegexPattern::DOTALL
            | RegexPattern::COMMENTS
            | RegexPattern::UTF8;
        $binds = ['@bind1' => '~value1', 'bind2' => '#value2'];
        $this->assertTrue((new RegexPattern($regex, $flags, $binds))->isValid());
    }
    
    public function testMatch()
    {
        $this->assertTrue((new RegexPattern('^test$'))->match('test'));
        $this->assertTrue((new RegexPattern('^test$', RegexPattern::CASE_INSENSITIVE))->match('TEST'));
        $this->assertFalse((new RegexPattern('^test$'))->match(' test '));
        
        $regex = '^[T]est~@bind1\\\\bind2$';
        $flags = RegexPattern::CASE_INSENSITIVE
            | RegexPattern::MULTILINE
            | RegexPattern::DOTALL
            | RegexPattern::COMMENTS
            | RegexPattern::UTF8;
        $binds = ['@bind1' => '~value1', 'bind2' => '#value2'];
        $this->assertTrue((new RegexPattern($regex, $flags, $binds))->match('Test~~value1\\#value2'));
    }
    
    public function testMatchRegexException()
    {
        $this->expectException(RegexException::class);
        (new RegexPattern('['))->match('[');
    }
    
    public function testMatchRegexException2()
    {
        $this->expectException(RegexException::class);
        (new RegexPattern("foo", RegexPattern::UTF8))->match("\xf8\xa1\xa1\xa1\xa1");
        $this->expectExceptionMessageMatches('/Malformed.*/');
    }
    
    public function testToString()
    {
        $regex = '^[T]est~@bind1\\\\bind2$';
        $flags = RegexPattern::CASE_INSENSITIVE
            | RegexPattern::MULTILINE
            | RegexPattern::DOTALL
            | RegexPattern::COMMENTS
            | RegexPattern::UTF8;
        $binds = ['@bind1' => '~value1', 'bind2' => '#value2'];
        
        $pattern1 = new RegexPattern($regex);
        $pattern2 = new RegexPattern($regex, $flags);
        $pattern3 = new RegexPattern($regex, $flags, $binds);
        
        $delimiter = RegexPattern::DELIMITER;
        
        $exp1 = $delimiter . '^[T]est\~@bind1\\\\bind2$' . $delimiter;
        $exp2 = $delimiter . '^[T]est\~@bind1\\\\bind2$' . $delimiter . 'imsxu';
        $exp3 = $delimiter . '^[T]est\~\~value1\\\\\#value2$' . $delimiter . 'imsxu';
        
        $this->assertSame($exp1, (string) $pattern1);
        $this->assertSame($exp2, (string) $pattern2);
        $this->assertSame($exp3, (string) $pattern3);
    }
}
