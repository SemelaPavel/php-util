<?php
use SemelaPavel\File\File;
use SemelaPavel\File\FileFilter;
use SemelaPavel\Object\Byte;
?>

<h2>&lt;namespace&gt; File</h2>
<h3>&lt;class&gt; File</h3>

<table style="border-collapse: collapse" border="1">
    <tr>
        <td>File::rtrimFileName('file.txt/'):</td>
        <td><?= File::rtrimFileName('file.txt/') ?></td>
    </tr>
    <tr>
        <td>File::rtrimFileName('file.txt  .'):</td>
        <td><?= File::rtrimFileName('file.txt  .') ?></td>
    </tr>
    <tr>
        <td>File::rtrimFileName("file.txt.\xFF"):</td>
        <td><?= File::rtrimFileName("file.txt.\xFF") ?></td>
    </tr>
</table>
<br>

<table style="border-collapse: collapse" border="1">
    <tr>
        <td>File::isValidFileName('file.txt/'):</td>
        <td><?= File::isValidFileName('file.txt/') ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>File::isValidFileName('file.txt  .'):</td>
        <td><?= File::isValidFileName('file.txt  .') ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>File::isValidFileName("file.txt.\xFF"):</td>
        <td><?= File::isValidFileName("file.txt.\xFF") ? 'true' : 'false'; ?></td>
    </tr>
</table>
<br>

<?php $file = new File('.htaccess'); ?>

<table style="border-collapse: collapse" border="1">
    <tr>
        <th colspan="2" style="text-align: left">$file = new File('.htaccess')</th>
    </tr>
    <tr>
        <td>$file->getContents():</td>
        <td><em><?= $file->getContents();  ?></em></td>
    </tr>
    <tr>
        <td>$file->getMimeType():</td>
        <td><?= $file->getMimeType(); ?></td>
    </tr>
    <tr>
        <td>$file->hasValidName():</td>
        <td><?= $file->hasValidName() ? 'true' : 'false'; ?></td>
    </tr>
</table>
<br>
<?php $file = new File('nonexistentfile.txt'); ?>

<table style="border-collapse: collapse" border="1">
    <tr>
        <th colspan="2" style="text-align: left">$file = new File('nonexistentfile.txt')</th>
    </tr>
    <tr>
        <td>$file->getContents():</td>
        <td>
        <?php
        try {
            $file->getContents();
        } catch (\RuntimeException $e) {
            echo $e->getMessage();
        }
        ?>
        </td>
    </tr>
    <tr>
        <td>$file->getMimeType():</td>
        <td><?php $mime = $file->getMimeType(); echo $mime === null ? 'null' : $mime; ?></td>
    </tr>
    <tr>
        <td>$file->hasValidName():</td>
        <td><?= $file->hasValidName() ? 'true' : 'false'; ?></td>
    </tr>
</table>

<h3>&lt;class&gt; FileFilter</h3>
<?php
$filter = (new FileFilter())
    ->setFileNameWhiteList(['*.jpg', '*.png', '*.gif'])
    ->setFileNameBlackList(['*.php.*'])
    ->setFileNameRegex(new \SemelaPavel\String\RegexPattern('^[^0-9]*$'))
    ->setFileSize('>1 KB < 1 MB')
    ->setMTime('>= 2021-01-01 < 2021-01-02 12:00');
?>
<table style="border-collapse: collapse" border="1">
    <tr>
        <th>&nbsp;</th><th>Parameters</th>
    </tr>
    <tr>
        <td><strong>File Name WhiteList:</strong></td>
        <td>['*.jpg', '*.png', '*.gif']</td>
    </tr>
    <tr>
        <td><strong>File Name BlackList:</strong></td>
        <td>['*.php.*']</td>
    </tr>
    <tr>
        <td><strong>File Name Regex:</strong></td>
        <td>new RegexPattern('^[^0-9]*$')</td>
    </tr>
    <tr>
        <td><strong>Set File Size:</strong></td>
        <td>&gt;1 KB &lt; 1 MB</td>
    </tr>
    <tr>
        <td><strong>Set MTime:</strong></td>
        <td>&gt;= 2021-01-01 &lt; 2021-01-02 12:00</td>
    </tr>
</table>
<br>
<table style="border-collapse: collapse; float: left" border="1">
    <tr>
        <th>File name</th><th>Match</th>
    </tr>
    <tr>
        <td>image.jpg</td>
        <td><?= $filter->fileNameMatch('image.jpg') ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>image1.jpg</td>
        <td><?= $filter->fileNameMatch('image1.jpg') ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>image.php.jpg</td>
        <td><?= $filter->fileNameMatch('image.php.jpg') ? 'true' : 'false'; ?></td>
    </tr>
</table>

<table style="border-collapse: collapse; float: left; margin-left: 30px" border="1">
    <tr>
        <th>File size</th><th>Match</th>
    </tr>
    <tr>
        <td>1 KB</td>
        <td><?= $filter->compareFileSize(Byte::parse('1 KB')->getValue()) ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>500 KB</td>
        <td><?= $filter->compareFileSize(Byte::parse('500 KB')->getValue()) ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>1 MB</td>
        <td><?= $filter->compareFileSize(Byte::parse('1 MB')->getValue()) ? 'true' : 'false'; ?></td>
    </tr>
</table>

<table style="border-collapse: collapse; float: left; margin-left: 30px" border="1">
    <tr>
        <th>MTime</th><th>Match</th>
    </tr>
    <tr>
        <td>2020-12-31</td>
        <td><?= $filter->compareMTime('2020-12-31') ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>2021-01-01</td>
        <td><?= $filter->compareMTime('2021-01-01') ? 'true' : 'false'; ?></td>
    </tr>
    <tr>
        <td>2021-01-02 13:00</td>
        <td><?= $filter->compareMTime('2021-01-02 13:00') ? 'true' : 'false'; ?></td>
    </tr>
</table>