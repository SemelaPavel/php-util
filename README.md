php-util library
================
[![Latest Stable Version](https://poser.pugx.org/semelapavel/php-util/v)](https://github.com/SemelaPavel/php-util/releases)
[![License](https://poser.pugx.org/semelapavel/php-util/license)](https://github.com/SemelaPavel/php-util/blob/master/LICENSE)

About
-----
PHP-Util library is a set of useful PHP classes that make life easier.

Installation
------------
Use the package manager [composer](https://getcomposer.org) to install php-util.

```shell
composer require semelapavel/php-util
```

Table of contents
-----------------
* [Object](#object)
    * [ClassLoader](#objectclassloader)
    * [Byte](#objectbyte)
* [File](#file)
    * [File](#filefile)
    * [FileFilter](#filefilefilter)
* [Http](#http)
    * [FileUpload](#httpfileupload)
* [Pagination](#pagination)
    * [Paginator](#paginationpaginator)
    * [Pagination](#paginationpagination)
* [String](#string)
    * [RegexPattern](#stringregexpattern)
* [Time](#time)
    * [Holidays](#timeholidays)
    * [Calendar](#timecalendar)
    * [LocalDateTime](#timelocaldatetime)


Object
======

Object\ClassLoader
------------------ 
A simple PSR-4 autoloader that loads files with required classes using namespaces.

```php
$classLoader = new \SemelaPavel\Object\ClassLoader();
$classLoader->addNamespace('MyNamespace', '/src/myApp');
$classLoader->addDirectory('/src/other');
$classLoader->register();
```

In this example, classLoader will try to find each file with a fully-qualified
class name starting with the prefix `MyNamespace` in the `/src/myApp` directory:
`\MyNamespace\Class` should be found as `/src/myApp/Class.php`.

If the file cannot not be found, or if it does not have `MyNamespace` prefix,
the classloader will try to find a file by the fully-qualified class name
in `/src/other` directory: `\MyNamespace\Class` should be found as `/src/other/MyNamespace/Class.php`.

Object\Byte
-----------
This class wraps an integer value and represents it as a binary byte.

```php
$byte = new Byte(1024);
echo $byte;                      // 1024
echo $byte->floatValue('KB');    // 1
echo $byte->floatValue('MB', 5); // 0,00098
```

Byte of any value and its specified binary unit:
```php
$byte = Byte::from(2.5, 'MB'); // 2621440
```

Easy parsing of php.ini values:
```php
$byte = Byte::fromPhpIniNotation(ini_get('upload_max_filesize'));
```

Parsing byte value from any string:
```php
$byte = Byte::parse('8');     // 8
$byte = Byte::parse('16B');   // 16
$byte = Byte::parse('1 KiB'); // 1024
$byte = Byte::parse('0,5MB'); // 524288
```


File
====
The File namespace contains a set of classes for working with files.

File\File
---------
An instance of this class represents a file in the file system. The file does not need to exist,
or be readable when creating a File object. This class extends
[\SplFileInfo](https://www.php.net/manual/en/class.splfileinfo.php) class from **Standard PHP Library** (SPL).

```php
$file = new File('file.txt');
echo $file->getMimeType();       // 'text/plain'
$content = $file->getContents(); // Reads the file and returns its contents as a string.
```

Check if the file name is safe and valid for most used operating systems:
```php
$file->hasValidName(); // Returns true in this example.
```

and without creating an object:
```php
File::isValidFileName('file.txt/'); // false
```

Strip the ASCII control characters, whitespaces, slashes, dots and backslashes from the end of file name:
```php
File::rtrimFileName('file.txt/'); // Returns 'file.txt' in this example.
```

File\FileFilter
---------------
An instance of this class helps filter out unwanted files by file names using
shell wildcards or a regular expression, files with a file size out of set size
or range and files with specific date and time or date-time range.

```php
$filter = new FileFilter();
$filter->setFileNameWhiteList(['*.jpg', '*.png', '*.gif']);
$filter->setFileNameBlackList(['*.php.*']);
$filter->setFileNameRegex(new \SemelaPavel\String\RegexPattern('^[^0-9]*$'));
$filter->setFileSize('>1 KB < 1 MB');
$filter->setMTime('>= 2021-01-01 < 2021-01-02 12:00');
```

Lets have `image.jpg` with file size  `4 KB` and modif. time `2021-01-01`:

```php
$filter->fileNameMatch('image.jpg')        // true
$filter->compareFileSize(4096);            // true
$filter->compareMTime('2021-01-01 13:37'); // true
```


Http
====
The Http namespace contains a set of classes handling requests and responses over HTTP.

Http\FileUpload
---------------
The `FileUpload` provides basic functionality for retrieving normalized file upload data for further processing.
Each leaf of the files array is an instance of `UploadedFile` or `null` if error `UPLOAD_ERR_NO_FILE` occured.

See simplified code below:

```html
<form action="" method="post" enctype="multipart/form-data">
    Select file to upload:
    <input type="file" name="file"><br>
    Select file to upload:
    <input type="file" name="filesArray[]"><br>
    Select file to upload:
    <input type="file" name="filesArray[]"><br>
    <input type="submit" value="Upload files" name="upload_submit">
</form>
```

```php
$upload = new FileUpload();
$files = $upload->getUploadedFiles();

if ($files['file']) {
    $files['file']->move(dirname(__DIR__) . '/upload/', 'newFile.txt'); // move the file with rename
}

if ($files['filesArray'][0]) {
    $files['filesArray'][0]->move(dirname(__DIR__) . '/upload/');
}
```


Pagination
==========
A set of useful classes for managing pagination of your web pages.

Pagination\Paginator
--------------------
Simple pagination calculator to help with calculate number of pages for specific number of items, number of items per page, offset etc.
See example below with total number of items set to 100, items per page set to 5
and current page set to 5.

```php
$paginator = new Paginator(100, 5, 5);

$paginator->getCurrentPage();       // 5
$paginator->getNumOfPages();        // 20
$paginator->getCurrentPageLength(); // 5 = number of page items
$paginator->getOffset();            // 20 = current page contains items nr. 21-25
$paginator->getFirstPage();         // 1
$paginator->getLastPage();          // 20 
$paginator->isFirst();              // false 
$paginator->isLast();               // false
$paginator->getNextPage();          // 6
$paginator->getPrevPage();          // 4
```

Pagination\Pagination
---------------------
`Paginator` extension that adds method to get an array of pages for advanced pagination.
See example below with total number of items set to 100, items per page set to 5
and current page set to 5.

```php
$pagination = new Pagination(100, 5, 5);
$pages = $pagination->toArray(1, 2);
```
The `$pages` array structure will looks like:
```php
[
    ['page' => 1, 'isCurrent' => false],
    ['page' => null, 'isCurrent' => false],
    ['page' => 3, 'isCurrent' => false],
    ['page' => 4, 'isCurrent' => false],
    ['page' => 5, 'isCurrent' => true],
    ['page' => 6, 'isCurrent' => false],
    ['page' => 7, 'isCurrent' => false],
    ['page' => null, 'isCurrent' => false],
    ['page' => 20, 'isCurrent' => false]
]   
```

String
======

String\RegexPattern
-------------------
This class represents a regular expression pattern.

Simple pattern example:
```php
$pattern = new RegexPattern('[a-z]{3}', RegexPattern::CASE_INSENSITIVE);

if ($pattern->isValid()) {
    \preg_match((string) $pattern, 'aBc');  // 1
    $pattern->match('aBc');                 // true
}
```

Pattern with bind example:
```php
$pattern = new RegexPattern('[a-z]{3}bind', 0, array('bind' => '.value'));
echo $pattern; // ~[a-z]{3}\.value~
```


Time
====

Time\Holidays
-------------
`ArrayAccess` class which handles holidays and provides some extra functionality.

```php
$holidays = new Holidays();
$holidays[Holidays::goodFriday(2021)] = "Good Friday";
$holidays[Holidays::easterMonday(2021)] = "Easter Monday";
$holidays["2021-05-01"] = "May Day";

echo $holidays['2021-05-01']; // "May Day"

foreach ($holidays->toArray() as $date => $description) { 
    echo $date . ': ' . $description . '<br>';
}
```

Time\Calendar
-------------
This class helps with date calculations.

```php
$today = new \DateTime();
Calendar::isDayOff($today);
$prevWorkday = Calendar::prevWorkday($today);
$nextWorkday = Calendar::nextWorkday($today);
$lastDayOfPrevMonth = Calendar::lastDayOfPrevMonth($today);
$lastDayOfMonth = Calendar::lastDayOfMonth($today);
```

With `Holidays` object set:
```php
$holidays = new Holidays();
$holidays["2021-05-01"] = "May Day";

$today = new \DateTime();
Calendar::isDayOff($today, holidays);
$prevWorkday = Calendar::prevWorkday($today, holidays);
$nextWorkday = Calendar::nextWorkday($today, holidays);
```

Time\LocalDateTime
------------------
This class is a `DateTime` factory. All functions in this class use default time zone.

```php
LocalDateTime::setLocalTimeZone(new \DateTimeZone('Europe/Prague'));

$dateTimeStr = ' 2020-  07 - 06 T 13 :37 : 00 . 001337  + 02: 00 '; 
$normalizedDateTimeStr = LocalDateTime::normalize($dateTimeStr); // "2020-07-06T13:37:00.001337+02:00"

$localDateTime = LocalDateTime::parse($normalizedDateTimeStr);
$now = LocalDateTime::now();
$today = LocalDateTime::today();

$now->format(LocalDateTime::SQL_DATETIME)  // 'Y-m-d H:i:s' format
```
