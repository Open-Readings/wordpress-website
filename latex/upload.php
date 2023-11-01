<?php
if(!isset($_SESSION['id'])) {
    session_start();
    $_SESSION['id'] = 1;
}
if(!isset($_SESSION['file'])) {
    $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
    shell_exec('mkdir ' . $_SESSION['file']);
    shell_exec('mkdir ' . $_SESSION['file'] . "/images");
}

$targetDirectory = $_SESSION['file'] . "/images/";

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
if ($fileType != "png" && $fileType != "jpeg") {
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
}
?>