<?php
/*
 * This file is part of the php-util package.
 *
 * (c) Pavel Semela <semela_pavel@centrum.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SemelaPavel\Object;

/**
 * PSR-4 Autoloader class. Loads files with required classes.
 * 
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class ClassLoader
{
    /** @var string */
    protected $namespacePrefix;

    /** @var string */
    protected $baseDir;

    /**
     * Initializes new classloader for the given name space and the given
     * base directory. Namespace prefix is a part of fully qualified class
     * name that is not used in directory structure.
     * 
     * @param string $namespacePrefix Namespace prefix.
     * @param string $baseDir Base directory where autoloader can find classes.
     */
    public function __construct($namespacePrefix, $baseDir)
    {
        // normalize namespace prefix
        $this->namespacePrefix = trim($namespacePrefix, '\\') . '\\';
        
        // normalize the base directory with current directory separator 
        $baseDir = rtrim($baseDir, "\x00..\x1F \x7F\xFF\\/");
        $toSearch = DIRECTORY_SEPARATOR == '/' ? '\\' : '/';
        $this->baseDir = str_replace($toSearch, DIRECTORY_SEPARATOR, $baseDir);     
    }

    /**
     * Registers autoloader.
     */
    public function register()
    {
        spl_autoload_register(array($this, "loadClass"));
    }

    /**
     * Unregisters autoloader.
     */
    public function unRegister()
    {
        spl_autoload_unregister(array($this, "loadClass"));
    }

    /**
     * Method to be registered with spl_autoload to automatically including
     * files with required classes.
     * 
     * @param string $class The fully-qualified class name.
     * 
     * @return bool True on success, False if class file cannot be loaded.
     */
    public function loadClass($class)
    {
        // normalize class name
        $class = ltrim($class, '\\');
        
        if (strpos($class, $this->namespacePrefix) === 0) {
            
            $classFile = $this->classFile(
                substr($class, strlen($this->namespacePrefix))
            );
        
            return $this->requireFile($classFile);
        } else {
            
            return false;
        }
    }

    /**
     * Returns file path of the class.
     * 
     * @param string $class The fully-qualified or relative class name.
     * 
     * @return string File path.
     */
    protected function classFile($class)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        
        return $this->baseDir . DIRECTORY_SEPARATOR . $classPath . ".php";
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * 
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile($file)
    {
        if (is_file($file)) {
            require $file;
            
            return true;
        } else {
            
            return false;
        }
    }
}
