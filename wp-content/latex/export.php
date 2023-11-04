<?php

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

if(!is_dir(__DIR__ . '/' . $_SESSION['file'])) {
    shell_exec('/bin/mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '"');
    shell_exec('/bin/mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '/images"');
}



$folder = $_SESSION['file'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $startOfDocument = '\documentclass[12pt, twoside, a4paper, hidelinks]{article}

    \usepackage{amsmath}
    \usepackage[T1]{fontenc}
    \usepackage{graphicx}
    \usepackage[utf8]{inputenc}
    \usepackage[left=3cm,right=1cm,top=3cm,bottom=2cm]{geometry}
    \usepackage{tikz}
    \usepackage{float}
    \usepackage{blindtext}
    \usepackage[1]{pagesel}
    \addtolength\oddsidemargin{-1cm} \addtolength\evensidemargin{1cm}
    \graphicspath{ {images/} }
    \usepackage{indentfirst}
    \usepackage{caption}
    \captionsetup[table]{labelsep=period}
    \captionsetup[figure]{labelsep=space}
    \pagestyle{empty}
    \begin{document}
    ';


    $authors = '\begin{center} ';
    $i = 0;
    foreach($_POST['name'] as $name){
        $authors = $authors . $name . '$^{' . $_POST['aff_ref'][$i] . '}$';
        if ($i < count($_POST['name']) - 1)
            $authors = $authors . ', ';
        $i++;
    }
    $authors = $authors . ' \end{center}

    ';


    $affiliations = '\begin{center} ';
    $i = 1;
    foreach($_POST['affiliation'] as $aff){
        $affiliations = $affiliations . '$^{' . $i . '}$' . $aff . '
        
        ';
        $i++;
    }
    $affiliations = $affiliations . $_POST['email-author'] . '
    \end{center}

    ';


    $references = '';
    $i = 1;
    foreach($_POST['references'] as $ref){
        $references = $references . '\setcounter{footnote}{' . $i . '} ' . '\footnotetext{' . $ref . '}
        ';
        $i++;
    }


    $titleField = $_POST['form_fields']['abstract_title'];
    $titleField = str_replace('<sup>', '$^{', $titleField);
    $titleField = str_replace('</sup><sub>', '}_{', $titleField);
    $titleField = str_replace('</sup>', '}$', $titleField);
    $titleField = str_replace('<sub>', '$_{', $titleField);
    $titleField = str_replace('</sub><sup>', '}^{', $titleField);
    $titleField = str_replace('</sub>', '}$', $titleField);
    $titleField = str_replace('&nbsp;', '', $titleField);

    $title = "\begin{center} {\large \\textbf{" . $titleField . "}} \\end{center}
    " ;


    $abstractContent = $_POST["textArea"];


    $endOfDocument = '
    \end{document}';


    $textData = $startOfDocument . $title . $authors . $affiliations . $abstractContent . $references . $endOfDocument;

    $filename = $folder . "/3.tex";
    file_put_contents($filename, $textData);
    shell_exec('/bin/pdflatex -interaction=nonstopmode --output-directory="' . __DIR__ . '/' . $folder . '" "' . __DIR__ . '/' . $folder . '/3.tex"');
}}
?>