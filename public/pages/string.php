<?php
use SemelaPavel\String\RegexPattern;
?>

<h2>&lt;namespace&gt; String</h2>
<h3>&lt;class&gt; Regex</h3>

<table style="border-collapse: collapse" border="1">
    <tr><th>File name</th><th>Glob pattern</th><th>Dir separator(s)</th><th>Regex pattern</th><th>preg_match</th></tr>
    <?php
    $fileName = '/home/user/file1.txt';
    $glob = '**/file[1-9].*';
    $separator = '/';
    $regexPattern = RegexPattern::fromGlob($glob, $separator);
    ?>
    <tr>
        <td><?= $fileName ?></td>
        <td><?= $glob ?></td>
        <td style="text-align: center"><?= $separator ?></td>
        <td><?= htmlspecialchars($regexPattern) ?></td>
        <td style="text-align: center"><?= $regexPattern->match($fileName) ? 'true' : 'false' ?></td>
    </tr>
    <?php
    $fileName = 'file1.txt';
    $glob = 'file[!1-9].txt';
    $separator = '/';
    $regexPattern = RegexPattern::fromGlob($glob);
    ?>
    <tr>
        <td><?= $fileName ?></td>
        <td><?= $glob ?></td>
        <td style="text-align: center"><?= $separator ?></td>
        <td><?= htmlspecialchars($regexPattern) ?></td>
        <td style="text-align: center"><?= $regexPattern->match($fileName) ? 'true' : 'false' ?></td>
    </tr>
    <?php
    $fileName = 'C:\\\\documents\\file1.txt';
    $glob = 'C:\\\\**file[1-9].txt';
    $separator = '\\';
    $regexPattern = RegexPattern::fromGlob($glob, '\\');
    ?>
    <tr>
        <td><?= $fileName ?></td>
        <td><?= $glob ?></td>
        <td style="text-align: center"><?= $separator ?></td>
        <td><?= htmlspecialchars($regexPattern) ?></td>
        <td style="text-align: center"><?= $regexPattern->match($fileName) ? 'true' : 'false' ?></td>
    </tr>
    <?php
    $fileName = 'C:\\\\documents/file1.txt';
    $glob = 'C:\\\\**file[1-9].txt';
    $separator = '\\/';
    $regexPattern = RegexPattern::fromGlob($glob, '\\/');
    ?>
    <tr>
        <td><?= $fileName ?></td>
        <td><?= $glob ?></td>
        <td style="text-align: center"><?= $separator ?></td>
        <td><?= htmlspecialchars($regexPattern) ?></td>
        <td style="text-align: center"><?= $regexPattern->match($fileName) ? 'true' : 'false' ?></td>
    </tr>
    <?php
    $fileName = 'C:\\\\documents/file1.txt';
    $glob = 'C:\\\\*file[1-9].txt';
    $separator = '\\/';
    $regexPattern = RegexPattern::fromGlob($glob, '\\/');
    ?>
    <tr>
        <td><?= $fileName ?></td>
        <td><?= $glob ?></td>
        <td style="text-align: center"><?= $separator ?></td>
        <td><?= htmlspecialchars($regexPattern) ?></td>
        <td style="text-align: center"><?= $regexPattern->match($fileName) ? 'true' : 'false' ?></td>
    </tr>
    <?php
    $fileName = 'file\\.txt';
    $glob = 'file*.txt';
    $separator = '\\/';
    $regexPattern = RegexPattern::fromGlob($glob, '\\/');
    ?>
    <tr>
        <td><?= $fileName ?></td>
        <td><?= $glob ?></td>
        <td style="text-align: center"><?= $separator ?></td>
        <td><?= htmlspecialchars($regexPattern) ?></td>
        <td style="text-align: center"><?= $regexPattern->match($fileName) ? 'true' : 'false' ?></td>
    </tr>
 </table>
