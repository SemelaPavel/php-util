<?php
require dirname(__DIR__) . '/config/bootstrap.php';
?>

<html>
    <head>
        <link rel="stylesheet" href="/styles/pagination.css">
    </head>
    <body>
        <h1>PHP-Util</h1>
        <ul>
            <li><a href="?page=time">Time</a></li>
            <li><a href="?page=pagination">Pagination</a></li>
        </ul>
<?php
$options = array('options' => array('default'=> 'time')); 
$page = filter_input(INPUT_GET, 'page', FILTER_DEFAULT, $options);

include dirname(__DIR__) . "/public/{$page}.php";
?>

    </body>
</html>