<?php
mb_internal_encoding('UTF-8');

$baseDir = dirname(__DIR__) . "/src";

require $baseDir . "/Object/ClassLoader.php";

$classLoader = new \SemelaPavel\Object\ClassLoader();
$classLoader->addNamespace('SemelaPavel', $baseDir);
$classLoader->register();
