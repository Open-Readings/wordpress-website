<?php

error_reporting(0);


function check_abstract_fields(){
    $author_names = $_POST['name'];
    $author_affiliations = $_POST['aff_ref'];
    $author_radio = $_POST['contact_author'];
    $author_email = $_POST['email-author'];
    $affiliations = $_POST['affiliation'];
    $abstract_content = $_POST['textArea'];

    $pattern = '/[^\\p{L} ]/u';
    foreach ($author_names as $author_name){
        if (strlen($author_name) > 15){
            return "Author name too long.";
        } else if (preg_match($pattern, $author_name)){
            return "Special characters not allowed in author name field.";
        } else if (trim($author_name) == '') {
            return "Author field can't be empty.";
        }
    }
    foreach ($affiliations as $affiliation){
        if (strlen($affiliation) > 100){
            return "Affiliation name too long.";
        } else if (preg_match($pattern, $affiliation)){
            return "Special characters not allowed in affiliation field.";
        } else if (trim($affiliation) == '') {
            return "Affiliation field can't be empty.";
        }
    }
    foreach ($author_affiliations as $author_affiliation){
        if (strlen($author_affiliation) > 100){
            return "Affiliation reference too long.";
        } else if (preg_match('[0-9, ]*', $author_affiliation)){
            return "Characters not allowed in affiliation reference field.";
        } else if (trim($author_affiliation) == '') {
            return "Affiliation reference field can't be empty.";
        }
    }

    return 0;
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

function generate_abstract(){

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
            shell_exec('mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '"');
            shell_exec('mkdir "' . __DIR__ . '/' . $_SESSION['file'] . '/images"');
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
                $name = trim($name);
                $name = preg_replace('/[^\p{L}\p{N}\s&]/', '', $name);
                $aff_ref = $_POST['aff_ref'][$i - 1];
                $aff_ref = trim($aff_ref);
                //replace everything that is not a digit or ,
                $aff_ref = preg_replace('/[^\d,]/', '', $aff_ref);

                if ($_POST['contact_author'] == $i)
                    $authors = $authors . '\underline{' . $name . '}$^{' . $aff_ref . '}$';
                else
                    $authors = $authors . $name . '$^{' . $aff_ref . '}$';

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

            

            $titleField = $_POST['form_fields']['abstract_title'];

            // Add missing </sup> tags
            $titleField = fixUnclosedTags($titleField, '<sup>', '</sup>');

            // Add missing </sub> tags
            $titleField = fixUnclosedTags($titleField, '<sub>', '</sub>');


            $titleField = preg_replace('/[^\p{L}\p{N}\s&<>;\/]/', '', $titleField);


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
            $abcd=shell_exec('/bin/pdflatex -interaction=nonstopmode --output-directory="' . $folder . '" "' . $folder . '/abstract.tex"');
            $_SESSION['generating'] = 0;

            if (file_exists(__DIR__ . '/' . $folder . '/abstract.pdf'))
                echo 'Export completed::0';
            else
                echo 'Export failed::1';
        }
    }
}
// generate_abstract();

$field_validity = check_abstract_fields();
if ($field_validity == 0)
    generate_abstract();
else if (file_exists(__DIR__ . '/' . $folder . '/abstract.pdf')){
    echo 'Export failed::' . $field_validity . '::end';
} else {
    echo 'Export failed::' . $field_validity . '::end';
}
?>