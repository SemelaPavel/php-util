<?php
error_reporting(E_ALL ^ E_DEPRECATED);

$baseDir = dirname(__DIR__) . "/src";

require $baseDir . "/Object/ClassLoader.php";

$classLoader = new SemelaPavel\Object\ClassLoader("SemelaPavel", $baseDir);
$classLoader->register();
