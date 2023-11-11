<?php

error_reporting(0);

if (!isset($_SESSION['id'])) {
    ini_set('session.gc_maxlifetime', 3600);
    session_start();
    $_SESSION['id'] = 1;
}

if (!isset($_SESSION['generating'])) {
    $_SESSION['generating'] = 0;
}

if ($_SESSION['generating'] == 0) {

    $_SESSION['generating'] = 1;

    if (!isset($_SESSION['file'])) {
        $timestamp = time();
        $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
    }

    if (!is_dir(__DIR__ . '/' . $_SESSION['file'])) {
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
    \usepackage[left=2cm,right=2cm,top=2cm,bottom=2cm]{geometry}
    \usepackage{tikz}
    \usepackage{float}
    \usepackage{blindtext}
    \usepackage[1]{pagesel}
    \graphicspath{ {images/} }
    \usepackage{indentfirst}
    \usepackage{caption}
    \captionsetup[table]{labelsep=period}
    \captionsetup[figure]{labelsep=space}
    \pagestyle{empty}
    \makeatletter
    \renewcommand{\fnum@figure}{Fig. \thefigure :}
    \makeatother
    \begin{document}
    ';


        $authors = '\begin{center} ';
        $i = 1;
        foreach ($_POST['name'] as $name) {
            if ($_POST['contact_author'] == $i)
                $authors = $authors . '\underline{' . $name . '}$^{' . $_POST['aff_ref'][$i - 1] . '}$';
            else
                $authors = $authors . $name . '$^{' . $_POST['aff_ref'][$i - 1] . '}$';

            if ($i < count($_POST['name']))
                $authors = $authors . ', ';
            $i++;
        }
        $authors = $authors . ' \end{center}
    \vspace{-.5cm}

    ';


        $affiliations = '\begin{center} {\small ';
        $i = 1;
        foreach ($_POST['affiliation'] as $aff) {
            $affiliations = $affiliations . '$^{' . $i . '}$' . $aff . '
        
        ';
            $i++;
        }
        $affiliations = $affiliations . '\underline{' . $_POST['email-author'] . '}
    } \end{center}

    ';


        $references = '';
        $i = 1;
        foreach ($_POST['references'] as $ref) {
            $references = $references . '\setcounter{footnote}{' . $i . '} ' . '\footnotetext{' . $ref . '}
        ';
            $i++;
        }

        function fixUnclosedTags($text, $tagOpen, $tagClose)
        {
            $countOpen = substr_count($text, $tagOpen);
            $countClose = substr_count($text, $tagClose);

            $tagDiff = $countOpen - $countClose;

            if ($tagDiff > 0) {
                $text .= str_repeat($tagClose, $tagDiff);
            }

            return $text;
        }

        $titleField = $_POST['form_fields']['abstract_title'];

        // Add missing </sup> tags
        $titleField = fixUnclosedTags($titleField, '<sup>', '</sup>');

        // Add missing </sub> tags
        $titleField = fixUnclosedTags($titleField, '<sub>', '</sub>');



        //find fist <sup> or <sub> tag

        $sup_starting_tag = '<sup>';
        $sub_starting_tag = '<sub>';
        $sub_ending_tag = '</sub>';
        $sup_ending_tag = '</sup>';
        $layers = 0;
        $is_in_math_mode = false;
        for ($i = 0; $i < strlen($titleField); $i++) {
            if (substr($titleField, $i, strlen($sup_starting_tag)) == $sup_starting_tag) {
                $sup_starting_tag_index = $i;
                $layers++;
                if ($layers == 1) {
                    $titleField = substr_replace($titleField, '$^{', $sup_starting_tag_index, strlen($sup_starting_tag));
                } else {
                    //replace <sup> with $^{
                    $titleField = substr_replace($titleField, '^{', $sup_starting_tag_index, strlen($sup_starting_tag));
                }
                $i -= strlen($sup_starting_tag);
            }
            if (substr($titleField, $i, strlen($sub_starting_tag)) == $sub_starting_tag) {
                $sub_starting_tag_index = $i;
                $layers++;
                if ($layers == 1) {
                    $titleField = substr_replace($titleField, '$_{', $sub_starting_tag_index, strlen($sub_starting_tag));
                } else {
                    //replace <sub> with $_{
                    $titleField = substr_replace($titleField, '_{', $sub_starting_tag_index, strlen($sub_starting_tag));
                }
                $i -= strlen($sup_starting_tag);

            }

            if (substr($titleField, $i, strlen($sub_ending_tag)) == $sub_ending_tag) {
                $sub_ending_tag_index = $i;
                $layers--;
                if ($layers == 0) {
                    //replace </sub> with }$
                    $titleField = substr_replace($titleField, '}$', $sub_ending_tag_index, strlen($sub_ending_tag));
                } else {
                    //replace </sub> with }$
                    $titleField = substr_replace($titleField, '}', $sub_ending_tag_index, strlen($sub_ending_tag));
                }
                //replace </sub> with }$
                $i -= strlen($sup_starting_tag);
            }
            if (substr($titleField, $i, strlen($sup_ending_tag)) == $sup_ending_tag) {
                $sup_ending_tag_index = $i;
                $layers--;
                if ($layers == 0) {
                    //replace </sup> with }$
                    $titleField = substr_replace($titleField, '}$', $sup_ending_tag_index, strlen($sup_ending_tag));
                } else {
                    //replace </sup> with }$
                    $titleField = substr_replace($titleField, '}', $sup_ending_tag_index, strlen($sup_ending_tag));
                }
                $i -= strlen($sup_starting_tag);
            }

        }



        $titleField = str_replace('&nbsp;', '', $titleField);

        $title = "\begin{center} \MakeUppercase{ {\large \\textbf{" . $titleField . "}}} \\end{center}
    \\vspace{-0.8cm}";


        $abstractContent = $_POST["textArea"];


        $endOfDocument = '
    \end{document}';


        $textData = $startOfDocument . $title . $authors . $affiliations . $abstractContent . $references . $endOfDocument;

    $filename = $folder . "/abstract.tex";
    file_put_contents($filename, $textData);
    shell_exec('/bin/pdflatex -interaction=nonstopmode --output-directory="' . $folder . '" "' . $folder . '/abstract.tex"');
    $_SESSION['generating'] = 0;

    if(file_exists(__DIR__ . '/' . $folder . '/abstract.pdf'))
        echo 'Export completed';
    else
        echo 'Export failed';
}}
?>