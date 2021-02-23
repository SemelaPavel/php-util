<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SemelaPavel\Object\ClassLoader;
use PHPUnit\Framework\TestCase;

/**
 * Mock ClassLoader does not require real files, but only tries
 * to find their full path in files array.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class MockClassLoader extends ClassLoader
{
    protected $files = [];

    /**
     * @param array $files Full paths to the files that ClassLoader should find.
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
    protected function requireFile($file): bool
    {
        return in_array($file, $this->files);
    }
}

/**
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
final class ClassLoaderTest extends TestCase
{
    protected $classLoader;
    
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
        $this->classLoader->addNamespace('Acme\Log\Writer', './acme-log-writer/lib2/');
        $this->classLoader->addNamespace('Aura\Web', '\path\to\aura-web\src');
        $this->classLoader->addNamespace('Symfony\Core', './vendor\Symfony\Core/');
        $this->classLoader->addNamespace('Zend', '/usr/includes/Zend/');
        $this->classLoader->addDirectory('\src\other\\');
        $this->classLoader->addDirectory('\src\other\packages\\');
        
        $this->classLoader->setFiles($files);
    }

    public function testLoadClass()
    {
        $this->assertTrue($this->classLoader->loadClass('\Acme\Log\Writer\File_Writer'));
        $this->assertTrue($this->classLoader->loadClass('\Acme\Log\Writer\File_Writer2'));
        $this->assertTrue($this->classLoader->loadClass('Aura\Web\Response\Status'));
        $this->assertTrue($this->classLoader->loadClass('\Symfony\Core\Request'));
        $this->assertTrue($this->classLoader->loadClass('Zend\Acl'));
        $this->assertTrue($this->classLoader->loadClass('Status'));
        $this->assertTrue($this->classLoader->loadClass('\Vendor\Symfony\Core\Request'));
        
        $this->assertFalse($this->classLoader->loadClass('Log\Writer\File_Writer'));
        $this->assertFalse($this->classLoader->loadClass('Response\Status'));
        $this->assertFalse($this->classLoader->loadClass('Symfony'));
        $this->assertFalse($this->classLoader->loadClass('Acl'));
        $this->assertFalse($this->classLoader->loadClass('\Response\Status'));
    }
}
