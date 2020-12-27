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
 * @version 2020-06-07
 */
final class ClassLoaderTest extends TestCase
{
    protected $classLoaders = [];
    
    protected function setUp(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        
        $this->addClassLoader(
            'Acme\Log\Writer', 
            "./acme-log-writer/lib/ \x7F", 
            [".{$ds}acme-log-writer{$ds}lib{$ds}File_Writer.php"]
        );
        $this->addClassLoader(
            'Aura\Web', 
            '\path\to\aura-web\src \\ ', 
            ["{$ds}path{$ds}to{$ds}aura-web{$ds}src{$ds}Response{$ds}Status.php"]
        );
        $this->addClassLoader(
            'Symfony\Core', 
            './vendor\Symfony\Core/', 
            [".{$ds}vendor{$ds}Symfony{$ds}Core{$ds}Request.php"]
        );
        $this->addClassLoader(
            'Zend', 
            "/usr/includes/Zend/ \xFF \\", 
            ["{$ds}usr{$ds}includes{$ds}Zend{$ds}Acl.php"]
        );
    }
    
    /**
     * @param string $namespacePrefix Namespace prefix.
     * @param string $baseDir Base directory where autoloader can find classes.
     * @param array $files Array with class files, that loader should return.
     */
    protected function addClassLoader($namespacePrefix, $baseDir, array $files)
    {
        $classLoader = new MockClassLoader($namespacePrefix, $baseDir);
        $classLoader->setFiles($files);
        $this->classLoaders[] = $classLoader;
    }
    
    /**
     * Finds ClassLoader that match with input class. 
     * 
     * @param string $class The fully-qualified class name.
     * @return bool True on success, False if class cannot be loaded.
     */
    protected function classLoaderMatch($class): bool
    {
        foreach ($this->classLoaders as $classLoader) {
            if ($classLoader->loadClass($class)) {
                
                return true;
            }
        }
        
        return false;
    }
    
    public function testLoadClass()
    {
        $this->assertTrue($this->classLoaderMatch('\Acme\Log\Writer\File_Writer'));
        $this->assertTrue($this->classLoaderMatch('\Aura\Web\Response\Status'));
        $this->assertTrue($this->classLoaderMatch('\Symfony\Core\Request'));
        $this->assertTrue($this->classLoaderMatch('\Zend\Acl'));
        $this->assertFalse($this->classLoaderMatch('\Zend/Acl'));
        $this->assertFalse($this->classLoaderMatch('\Zend\Acl\\'));
        $this->assertFalse($this->classLoaderMatch('\Core\Request'));
    }
}
