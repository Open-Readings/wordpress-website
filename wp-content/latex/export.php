<?php

// define('WP_USE_THEMES', false);
// require('wp-load.php');

if(!isset($_SESSION['id'])) {
    session_start();
    $_SESSION['id'] = 1;
}

if(!isset($_SESSION['file'])) {
    $timestamp = time();
    $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
}

if(!is_dir(__DIR__ . '/' . $_SESSION['file'])) {
    shell_exec('/bin/mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '"');
    shell_exec('/bin/mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '/images"');
}

$folder = $_SESSION['file'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $x = '\documentclass[12pt, twoside, a4paper, hidelinks]{article}

    \usepackage{amsmath}
    \usepackage[utf8]{inputenc}
    \usepackage{graphicx}
    \usepackage[L7x,T1]{fontenc}
    \usepackage[utf8]{inputenc}
    \usepackage[lithuanian]{babel}
    \usepackage[left=3cm,right=1cm,top=3cm,bottom=2cm]{geometry}
    \usepackage{lipsum}  
    \usepackage{tikz}
    \usepackage{circuitikz}
    \usepackage{float}
    \usepackage{vwcol}
    \usepackage{blindtext}
    \usepackage{hyperref}
    \usepackage{enumitem}
    \usepackage[1]{pagesel}
    \setlist{leftmargin=8mm}
    \addtolength\oddsidemargin{-1cm} \addtolength\evensidemargin{1cm}
    \graphicspath{ {images/} }
    \usepackage{indentfirst}
    \usepackage{caption}
    \captionsetup[table]{labelsep=period}
    \captionsetup[figure]{labelsep=space}
    \pagestyle{empty}
    \begin{document}
    ';

    $e = '
    \end{document}';
    $n = '\begin{center} ';
    $i = 1;
    foreach($_POST['name'] as $name){
        $n = $n . $name;
        if ($i < count($_POST['name']))
            $n = $n . ', ';
        $i++;
    }
    $n = $n . ' \end{center}

    ';

    $a = '\begin{center} ';
    $i = 1;
    foreach($_POST['reference'] as $name){
        $a = $a . $name . '
        
        ';
        $i++;
    }
    $a = $a . $_POST['email-author'] . '
    \end{center}

    ';

    $r = '
    ';
    $i = 1;
    foreach($_POST['references'] as $name){
        $r = $r . '\setcounter{footnote}{' . $i . '} ' . '\footnotetext{' . $name . '}
        ';
        $i++;
    }

    
    $title = "\begin{center} {\large \\textbf{" . $_POST['form_fields']['abstract_title'] . "}} \\end{center}
    " ;

    $textData = $x . $title . $n . $a . $_POST["textArea"] . $r . $e;

    $filename = $folder . "/3.tex";
    file_put_contents($filename, $textData);
    $xa = shell_exec('/bin/pdflatex -interaction=nonstopmode --output-directory="' . __DIR__ . '/' . $folder . '" "' . __DIR__ . '/' . $folder . '/3.tex"');
}
?>