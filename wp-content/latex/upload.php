<?php

define('WP_USE_THEMES', false);
require(dirname(dirname(__DIR__)) . '/wp-load.php');


if(!isset($_SESSION['id'])) {
    session_start();
    $_SESSION['id'] = 1;
}

if(!isset($_SESSION['generating'])){
    $_SESSION['generating'] = 0;
}

if($_SESSION['generating'] == 0){

if(!isset($_SESSION['file'])) {
    $timestamp = time();
    $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
}
if(!is_dir( __DIR__ . '/' . $_SESSION['file'])) {
    shell_exec('/bin/mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '"');
}

if(!is_dir( __DIR__ . '/' . $_SESSION['file']) . '/images') {
    shell_exec('/bin/mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '/images' . '"');
}
$targetDirectory = __DIR__ . '/' . $_SESSION['file'] . "/images/";



$files = glob($targetDirectory . '*'); // Get a list of all files in the directory

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file); // Remove the file
    }
}


for($i=1; $i<=2;$i++){
$filename = "fileToUpload" . $i;
$targetFile = $targetDirectory . basename($_FILES[$filename]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));



if ($_FILES[$filename]["size"] > 500000) {
    echo "File is too large.";
    $uploadOk = 0;
}
if (($fileType != "png" && $fileType != "jpeg") && $fileType != "jpg") {
    echo "Only PNG or JPEG files are allowed.";
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo "File was not uploaded.";
} else {
    if (move_uploaded_file($_FILES[$filename]["tmp_name"], $targetFile)) {
        echo "The file " . basename($_FILES[$filename]["name"]) . " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

function enqueue_custom_script() {
    // Define your PHP variable to be passed to JavaScript
    $php_variable = 'Hello from PHP!';

    // Localize the script with the PHP variable
    wp_localize_script('custom-script', 'php_vars', array(
        'variable' => 1,
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script');

}}
?>