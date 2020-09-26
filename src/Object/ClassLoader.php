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
     * @param string $namespacePrefix Namespace prefix.
     * @param string $baseDir Base directory where autoloader can find classes.
     */
    public function __construct($namespacePrefix, $baseDir)
    {
        // normalize namespace prefix
        $this->namespacePrefix = trim($namespacePrefix, '\\') . '\\';
        
        // normalize the base directory with current directory separator 
        $toSearch = DIRECTORY_SEPARATOR == '/' ? '\\' : '/';
        $this->baseDir = rtrim(
            str_replace($toSearch, DIRECTORY_SEPARATOR, $baseDir), 
            DIRECTORY_SEPARATOR
        );
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
     * @return bool True on success, False if class file cannot be loaded.
     */
    public function loadClass($class)
    {
        // normalize class name
        $class = ltrim($class, '\\');
        
        if ($this->checkNamespacePrefix($class)) {
            $classFile = $this->classFile($this->relClassName($class));
        
            return $this->requireFile($classFile);
        } else {
            
            return false;
        }
    }

    /**
     * Checks if class has the namespace prefix. If the namespace
     * prefix is not set, returns always true.
     * 
     * @param string $class The fully-qualified class name.
     * @return bool True if class matches the namespace prefix.
     */
    protected function checkNamespacePrefix($class)
    {
        if ($this->namespacePrefix != null) {
            $prefix = substr($class, 0, strlen($this->namespacePrefix));
            
            return  $prefix == $this->namespacePrefix;
        } else {
            
            return true;
        }
        
    }

    /**
     * Removes namespace prefix from fully-qualified class name.
     * 
     * @param string $class The fully-qualified class name.
     * @return string Relative class name.
     */
    protected function relClassName($class)
    {
        $prefix = preg_quote($this->namespacePrefix);
        $relClassName = preg_replace("/^$prefix/", '', $class);
        
        return $relClassName;
    }

    /**
     * Returns file path of the class.
     * 
     * @param string $class The fully-qualified or relative class name.
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
