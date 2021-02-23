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
 * $classLoader = new \SemelaPavel\Object\ClassLoader();
 * $classLoader->addNamespace('SemelaPavel', '/src/myApp');
 * $classLoader->addDirectory('/src/other');
 * $classLoader->register();
 * 
 * In this example, classLoader will try to find each file with a fully-qualified
 * class name starting with the prefix 'SemelaPavel' in the '/src/myApp' directory.
 * e.g. \SemelaPavel\Object\Byte should be found as '/src/myApp/Object/Byte.php'
 * 
 * If the file cannot not be found, or if it does not have 'SemelaPavel' prefix,
 * the classloader will try to find a file by the fully-qualified class name
 * in __DIR__ . '/src/other directory.
 * e.g. \Namespace1\Namespace2\Class should be found as '/src/other/Namespace1/Namespace2/Class.php'
 * 
 * It is possible to add more alternate directories for one namespace and
 * it is also possible to add more namespaces. It is also possible to add
 * directories where the loader will try to find files by their fully-qualified
 * class names.
 *  
 * @author Pavel Semela <semela_pavel@centrum.cz>
 */
class ClassLoader
{
    /**
     * Key-value pairs of namespace prefixes and their paths. Each namespace prefix
     * has subarray of all possible paths for this namespace prefix.
     * 
     * $prefixes['prefix1'][] = '/src';
     * $prefixes['prefix1'][] = '/src2';
     * 
     * @var array Namespace prefixes and their paths.
     */
    protected $prefixes = [];
    
    /**
     * Array of the first letters of the prefixes and the lengths of the prefixes
     * to make things little bit faster when searching for files by namespace prefixes.
     * 
     * $pLengths['p'][7];
     * 
     * @var array
     */
    protected $pLengths = [];
    
    /**
     * This array contains paths to search a file by fully-qualified class names.
     * 
     * $directories[] = '/src';
     * 
     * @var array 
     */
    protected $directories = [];
    
    /**
     * Adds namespace prefix and its base directory to the classloader
     * or adds an alternate namespace base directory to the previously added
     * namespace prefix. Namespace prefix is a starting part of fully-qualified
     * class name that is not used in directory structure.
     * 
     * @param string $prefix Namespace prefix.
     * @param string $baseDir Base directory where autoloader can find classes.
     */
    public function addNamespace($prefix, $baseDir)
    {
        $prefix = trim($prefix, "\\");
        
        $this->prefixes[$prefix][] = rtrim($baseDir, "\\/");
        $this->pLengths[$prefix[0]][strlen($prefix)] = true;
    }
    
    /**
     * Adds a directory where classloader should try to find classes
     * by their fully-qualified names.
     * 
     * @param string $dir Directory path.
     */
    public function addDirectory($dir)
    {
        $this->directories[] = rtrim($dir, "\\/");
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
     * Method to be registered with spl_autoload to automatically load
     * the file where the required class is defined.
     * 
     * @param string $class The fully-qualified class name.
     * 
     * @return bool True on success, False if class file cannot be loaded.
     */
    public function loadClass($class)
    {
        $class = ltrim($class, '\\');
        
        // Find file by the namespace prefix
        if ($this->pLengths) {
            $len = strlen($class);
            $pos = 0;

            while (false !== $pos = strrpos($class, '\\', $pos)) {
                if (isset($this->pLengths[$class[0]][$pos]) && $this->findFileByPrefix($class, $pos)) {
                    
                    return true;                   
                }
                $pos -= ($len + 1);
            }
        }
        
        // Find file by fully-qualified class name
        if ($this->directories && $this->findFileByFullPath($class)) {
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Loads the file where the required class is defined using the fully-qualified
     * class name and its namespace prefix.
     * 
     * @param string $class The fully-qualified class name.
     * @param string $prefixPos Position of the last char in the prefix including '\'.
     * 
     * @return boolean True if the file with class has been successfully loaded.
     */
    protected function findFileByPrefix($class, $prefixPos)
    {
        $prefix = substr($class, 0, $prefixPos);

        if (isset($this->prefixes[$prefix])) {

            $file = substr($class, $prefixPos + 1) . '.php';
            foreach ($this->prefixes[$prefix] as $dir) {
                if ($this->requireFile(str_replace('\\', '/', $dir . '/' . $file))) {

                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Loads the file where the required class is defined using the fully-qualified
     * class name as the file path.
     * 
     * @param string $class The fully-qualified class name.
     * 
     * @return boolean True if the file with class has been successfully loaded.
     */
    protected function findFileByFullPath($class)
    {
        $file = $class . ".php";
        foreach ($this->directories as $dir) {
            if ($this->requireFile(str_replace('\\', '/', $dir . '/' . $file))) {

                return true;
            }
        }
        
        return false;
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
