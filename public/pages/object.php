<?php
use SemelaPavel\Object\Byte;
?>

<h2>&lt;namespace&gt; Object</h2>

<h3>&lt;class&gt; Byte</h3>

<?php
$byteMax = new Byte(Byte::MAX_VALUE);
$byteFrom = Byte::from(2.5, 'MB');
$parsedByte = Byte::parse('1024 KiB');
$maxUploadFilesize = Byte::fromPhpIniNotation(ini_get('upload_max_filesize'));

?>
<table style="border-collapse: collapse" border="1">
    <tr>
        <th>&nbsp;</th><th>B</th><th>KB</th><th>MB</th><th>GB</th>
    </tr>
    <tr>
        <td><strong>From MAX_VALUE:</strong></td>
        <td><?= $byteMax; ?></td>
        <td><?= $byteMax->floatValue('KB'); ?></td>
        <td><?= $byteMax->floatValue('MB'); ?></td>
        <td><?= $byteMax->floatValue('GB', 5); ?></td>
    </tr>
    <tr>
        <td><strong>From value 2.5 of 'MB':</strong></td>
        <td><?= $byteFrom; ?></td>
        <td><?= $byteFrom->floatValue('KB'); ?></td>
        <td><?= $byteFrom->floatValue('MB'); ?></td>
        <td><?= $byteFrom->floatValue('GB', 5); ?></td>
    </tr>
    <tr>
        <td><strong>From parsed string '1024 KiB':</strong></td>
        <td><?= $parsedByte; ?></td>
        <td><?= $parsedByte->floatValue('KB'); ?></td>
        <td><?= $parsedByte->floatValue('MB'); ?></td>
        <td><?= $parsedByte->floatValue('GB', 5); ?></td>
    </tr>
    <tr>
        <td><strong>From ini_get('upload_max_filesize'):</strong></td>
        <td><?= $maxUploadFilesize; ?></td>
        <td><?= $maxUploadFilesize->floatValue('KB'); ?></td>
        <td><?= $maxUploadFilesize->floatValue('MB'); ?></td>
        <td><?= $maxUploadFilesize->floatValue('GB', 5); ?></td>
    </tr>
</table>
