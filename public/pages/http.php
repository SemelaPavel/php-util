<?php
use SemelaPavel\Http\FileUpload;
$tmpFiles = [];
?>

<h2>&lt;namespace&gt; Http</h2>
<h3>&lt;class&gt; FileUpload</h3>
<h3>&lt;class&gt; UploadedFile</h3>

<?php try { $upload = new FileUpload(); $error = ''; ?>
    <p>
        <form action="" method="post" enctype="multipart/form-data">
            Select file to upload:
            <input type="file" name="file" id="file"><br>
            Select file to upload:
            <input type="file" name="filesArray[1D]" id="files"><br>
            Select file to upload:
            <input type="file" name="filesArray[2D1][2D2]" id="files">
            <br>
            Select file to upload:
            <input type="file" name="filesArray[3D1][][3D3]" id="files">
            <br>
            <input type="submit" value="Upload files" name="upload_submit">
        </form>
    </p>
    <?php
    try {
        $uplFiles = $upload->getUploadedFiles();
        if ($uplFiles) {
            $tmpFiles[0] = $uplFiles['file'];
            $tmpFiles[1] = $uplFiles['filesArray']['1D'];
            $tmpFiles[2] = $uplFiles['filesArray']['2D1']['2D2'];
            $tmpFiles[3] = $uplFiles['filesArray']['3D1'][0]['3D3'];
        }
    } catch (\LengthException $e) {
        // Upload exceeds the post_max_size directive in php.ini.
        $error = $e->getMessage();
    }
} catch (\RuntimeException $e) {
    //File uploads not allowed.    
    $error = $e->getMessage();
}
?>
<p style="color: red">
<?= $error ?>
</p>
<?php
foreach ($tmpFiles as $tmpFile) {
    try {
        if ($tmpFile) {
            $tmpFile->move(dirname(__DIR__) . '/upload/');
        }
    } catch (\RuntimeException $e) { ?>

        <p style="color: red">
        <?= $e->getMessage(); ?>
        </p>
        
    <?php
    }
}
