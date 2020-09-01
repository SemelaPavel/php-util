<?php
$baseDir = dirname(__DIR__) . "/src";

require $baseDir . "/Object/ClassLoader.php";

$classLoader = new SemelaPavel\Object\ClassLoader("SemelaPavel", $baseDir);
$classLoader->register();
