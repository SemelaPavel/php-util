<h2>Time</h2>

<?php
use SemelaPavel\Time\Holidays;
use SemelaPavel\Time\Calendar;
use SemelaPavel\Time\LocalDateTime;

$year = Calendar::currentYear();
        
$holidays = new Holidays();
$holidays["{$year}-01-01"] = "New Year's Day";
$holidays[Holidays::goodFriday($year)] = "Good Friday";
$holidays[Holidays::easterMonday($year)] = "Easter Monday";
$holidays["{$year}-05-01"] = "May Day";
$holidays["{$year}-05-08"] = "Liberation Day";
$holidays["{$year}-07-05"] = "St Cyril and St Methodius Day";
$holidays["{$year}-07-06"] = "Jan Hus Day";
$holidays["{$year}-09-28"] = "Statehood Day";
$holidays["{$year}-10-28"] = "Independence Day";
$holidays["{$year}-11-17"] = "Freedom and Democracy Day";
$holidays["{$year}-12-24"] = "Christmas Eve";
$holidays["{$year}-12-25"] = "Christmas Day";
$holidays["{$year}-12-26"] = "2nd Day of Christmas";

?>
<h3>Holidays</h3>
<?php

foreach ($holidays->toArray() as $date => $description) { 
    echo $date . ': ' . $description . '<br>';
}

$today = new \DateTime();
$prevWorkday = Calendar::prevWorkday($today);
$nextWorkday = Calendar::nextWorkday($today);
$lastDayOfPrevMonth = Calendar::lastDayOfPrevMonth($today);
$lastDayOfMonth = Calendar::lastDayOfMonth($today);
?>
<h3>Calendar</h3>
    <table>
        <tr>
            <td>Current year:</td>
            <td><?= $year ?></td>
        </tr>
        <tr>
            <td>Last day of previous month:</td>
            <td><?= $lastDayOfPrevMonth->format('D Y-m-d') ?></td>
        </tr>
        <tr>
            <td>Previous workday:</td>
            <td><?= $prevWorkday->format('D Y-m-d') ?></td>
        </tr>
        <tr>
            <td>Today:</td>
            <td><?= $today->format('D Y-m-d') ?></td>
        </tr>
        <tr>
            <td>Next workday:</td>
            <td><?= $nextWorkday->format('D Y-m-d') ?></td>
        </tr>
        <tr>
            <td>Last day of month:</td>
            <td><?= $lastDayOfMonth->format('D Y-m-d') ?></td>
        </tr>
    </table>

<?php
$dateTimeStr = ' 2020-  07 - 06 T 13 :37 : 00 . 001337  + 02: 00 ';
$normalizedDateTimeStr = LocalDateTime::normalize($dateTimeStr);
$localDateTime = LocalDateTime::parse($normalizedDateTimeStr);
$now = LocalDateTime::now();
$today = LocalDateTime::today();
?>

<h3>LocalDateTime</h3>
    <table>
         <tr>
            <td>Now:</td>
            <td><?= $now->format(LocalDateTime::SQL_DATETIME . '.u') ?></td>
        </tr>
        <tr>
            <td>Today:</td>
            <td><?= $today->format(LocalDateTime::SQL_DATETIME . '.u') ?></td>
        </tr>
        <tr>
            <td>String to normalize:</td>
            <td>"<?= $dateTimeStr ?>"</td>
        </tr>
        <tr>
            <td>Normalized string:</td>
            <td><strong>"<?= $normalizedDateTimeStr ?>"</td>
        </tr>
    </table>
