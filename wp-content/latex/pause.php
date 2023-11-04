<?php

if (!isset($_SESSION['id'])) {
    session_start();
    $_SESSION['id'] = 1;
}
if (!isset($_SESSION['generating'])) {
    $_SESSION['generating'] = 0;
}

$_SESSION['generating'] = 1;
sleep(4);
$_SESSION['generating'] = 0;
