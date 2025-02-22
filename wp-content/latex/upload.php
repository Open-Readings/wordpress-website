<?php

define('WP_USE_THEMES', false);
require(dirname(dirname(__DIR__)) . '/wp-load.php');

$folder = $_COOKIE['folder_hash'];

$targetDirectory = __DIR__ . '/temp/' . $folder . "/images/";



$files = glob($targetDirectory . '*'); // Get a list of all files in the directory

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file); // Remove the file
    }
}
if (isset($filenames))
    unset($filenames);

$max_files = get_option('or_registration_max_images');
$max_files = $max_files ? $max_files : 2;



for ($i = 1; $i <= $max_files; $i++) {

    $filename = "fileToUpload" . $i;
    $targetFile = $targetDirectory . basename($_FILES[$filename]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $filenames[$i - 1] = basename($_FILES[$filename]["name"]);

    if ($_FILES[$filename]["size"] > 6000000) {
        $uploadOk = 0;
    }
    if (($fileType != "png" && $fileType != "jpeg") && $fileType != "jpg") {
        $uploadOk = 0;
    }
    if ($i > $max_files) {
        $uploadOk = 0;
    }
    if ($uploadOk == 0) {
    } else {
        move_uploaded_file($_FILES[$filename]["tmp_name"], $targetFile);
    }

}

?>