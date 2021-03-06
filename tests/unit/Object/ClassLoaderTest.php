<?php declare (strict_types = 1);
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\UnitTests\Object;

use \SemelaPavel\Object\ClassLoader;
use \PHPUnit\Framework\TestCase;

/**
 * Mock ClassLoader does not require real files, but only tries
 * to find their full path in files array.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class MockClassLoader extends ClassLoader
{
    protected array $files = [];

    /**
     * @param array<int, string> $files Full paths to the files that ClassLoader should find.
     * @return void
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * @param string $file Whole file path and file name. 
     * @return bool True if the file is found by ClassLoader.
     */
    protected function requireFile(string $file): bool
    {
        return in_array($file, $this->files);
    }
}

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 * 
 * @covers \SemelaPavel\Object\ClassLoader
 */
final class ClassLoaderTest extends TestCase
{
    protected MockClassLoader $classLoader;
    
    protected function setUp(): void
    {
        $files = [
            './acme-log-writer/lib/File_Writer.php',
            './acme-log-writer/lib2/File_Writer2.php',
            '/path/to/aura-web/src/Response/Status.php',
            './vendor/Symfony/Core/Request.php',
            '/usr/includes/Zend/Acl.php',
            '/src/other/Status.php',
            '/src/other/packages/Vendor/Symfony/Core/Request.php'
        ];
                
        $this->classLoader = new MockClassLoader();
        $this->classLoader->addNamespace('Acme\Log\Writer', './acme-log-writer/lib/');
        $this->classLoader->addNamespace('Acme\Log\Writer', './acme-log-writer/lib2\\');
        $this->classLoader->addNamespace('Aura\Web\\', '\path\to\aura-web\src');
        $this->classLoader->addNamespace('\Symfony\Core', './vendor\Symfony\Core/');
        $this->classLoader->addNamespace('Zend', '/usr/includes/Zend/');
        $this->classLoader->addDirectory('\src\other\\');
        $this->classLoader->addDirectory('\src\other\packages\\');
        
        $this->classLoader->setFiles($files);
    }

    public function classesProvider(): array
    {
        return [
            ['\Acme\Log\Writer\File_Writer', true],
            ['\Acme\Log\Writer\File_Writer2', true],
            ['Aura\Web\Response\Status', true],
            ['\Symfony\Core\Request', true],
            ['Zend\Acl', true],
            ['Status', true],
            ['\Vendor\Symfony\Core\Request', true],
            ['Log\Writer\File_Writer', false],
            ['Response\Status', false],
            ['Symfony', false],
            ['Acl', false],
            ['\Response\Status', false],
        ];
    }
    
    /**
     * @dataProvider classesProvider
     */
    public function testLoadClass(string $class, bool $result): void
    {
        $this->assertSame($result, $this->classLoader->loadClass($class));
    }
}
