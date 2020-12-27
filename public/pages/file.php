<?php
use SemelaPavel\File\File;
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