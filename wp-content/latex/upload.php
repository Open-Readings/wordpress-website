<?php

define('WP_USE_THEMES', false);
require(dirname(dirname(__DIR__)) . '/wp-load.php');


if (!isset($_SESSION['id'])) {
    ini_set('session.gc_maxlifetime', 3600);
    session_start();
    $_SESSION['id'] = 1;
}

if (!isset($_SESSION['generating'])) {
    $_SESSION['generating'] = 0;
}

if ($_SESSION['generating'] == 0) {

    if (!isset($_SESSION['file'])) {
        $timestamp = time();
        $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
    }
    if (!is_dir(__DIR__ . '/' . $_SESSION['file'])) {
        mkdir(__DIR__ . '/' . $_SESSION['file']);
        //shell_exec('sudo /bin/mkdir   -m 777 "' . __DIR__ . '/' . $_SESSION['file'] . '"');
    }

    if (!is_dir(__DIR__ . '/' . $_SESSION['file']) . '/images') {
        mkdir(__DIR__ . '/' . $_SESSION['file'] . '/images');
        //shell_exec('sudo /bin/mkdir -m 777 "' . __DIR__ . '/' . $_SESSION['file'] . '/images' . '"');
    }
    $targetDirectory = __DIR__ . '/' . $_SESSION['file'] . "/images/";



    $files = glob($targetDirectory . '*'); // Get a list of all files in the directory

    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file); // Remove the file
        }
    }
    if (isset($_SESSION['filenames']))
        unset($_SESSION['filenames']);

    $max_files = get_option('or_registration_max_images');
    $max_files = $max_files ? $max_files : 2;



    for ($i = 1; $i <= $max_files; $i++) {

        $filename = "fileToUpload" . $i;
        $targetFile = $targetDirectory . basename($_FILES[$filename]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $_SESSION['filenames'][$i - 1] = basename($_FILES[$filename]["name"]);



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
            if (move_uploaded_file($_FILES[$filename]["tmp_name"], $targetFile)) {
            } else {
            }
        }

    }
}
?>