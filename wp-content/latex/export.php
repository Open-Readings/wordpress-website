<?php

error_reporting(0);


function check_abstract_fields()
{
    $title_length = 200;
    $field_group = [
        ['name', 'Author name', 200, '/[^\\p{L}\-.,;() ]/u'],
        ['aff_ref', 'Affiliation number', 200, '[0-9, ]*'],
        ['email-author', 'Corresponding author email', 100, ''],
        ['affiliation', 'Affiliation', 200, '/[^\\p{L}0-9 <>.,()\-&*:;!$]/u'],
        ['textArea', 'Abstract content', 3000, ''],
        ['references', 'Reference', 350, '/[^\\p{L}0-9 <>.,()\-&:;!$]/u']
    ];



    foreach ($field_group as $item) {
        if (is_array($_POST[$item[0]])) {
            foreach ($_POST[$item[0]] as $field) {
                if (mb_strlen($field) > $item[2]) {
                    return $item[1] . ": field input too long";
                }
                if ($item[3] != '') if (preg_match($item[3], $field)) {
                    return $item[1] . " field: special characters not allowed in field.";
                }
                if (trim($field) == '') {
                    return $item[1] . ": detected empty field.";
                }
            }
        } else {
            $field = $_POST[$item[0]];
            if (mb_strlen($field) > $item[2]) {
                return $item[1] . ": field input too long";
            }
            if ($item[3] != '') if (preg_match($item[3], $field)) {
                return $item[1] . " field: special characters not allowed in field.";
            }
            if (trim($field) == '' && $item[0] != 'references') {
                return $item[1] . ": detected empty field.";
            }
        }
    }
    $title = $_POST['form_fields']['abstract_title'];
    if (mb_strlen($title) > $title_length) {
        return "Title field input too long";
    } else if (preg_match('/[^\p{L}\p{N}, +=<>^;:()*\-.\/]/u', $title)) {
        return "Title field: special characters not allowed in field.";
    } else if (trim($title) == '') {
        return "Abstact title: detected empty field.";
    }

    if (filter_var($_POST['email-author'], FILTER_VALIDATE_EMAIL) == false)
        return "Corresponding author email not valid";

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

function generate_abstract()
{

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
        $folder = $_SESSION['file'];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $startOfDocument = '\documentclass[12pt, twoside, a4paper, hidelinks]{article}

        \usepackage{amsmath}
        \usepackage{lmodern}
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
        \renewcommand{\footnotesize}{\fontsize{9pt}{10pt}\selectfont}
        \begin{document}
        ';


            $authors = '\begin{center} \fontsize{12}{13}\selectfont ';
            $i = 1;
            foreach ($_POST['name'] as $name) {
                $name = trim($name);
                $name = preg_replace('/[^\p{L}\-\s.,;]/u', '', $name);
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


            $affiliations = '\begin{center} \fontsize{10}{11}\selectfont ';
            $i = 1;
            foreach ($_POST['affiliation'] as $aff) {
                $affiliations = $affiliations . '$^{' . $i . '}$' . $aff . '
            
            ';
                $i++;
            }
            $affiliations = $affiliations . '\underline{' . $_POST['email-author'] . '}
         \end{center}

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


            $titleField = preg_replace('/[^\p{L}\p{N}\s&\-+()=.:,<>;\/]/', '', $titleField);


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


            $title = "\begin{center}  \\fontsize{14}{15}\selectfont \\textbf{" . $titleField . "} \\end{center}
        \\vspace{-0.8cm}";


            $abstractContent = '\fontsize{10}{11}\selectfont ' . $_POST["textArea"];


            $endOfDocument = '
        \end{document}';


            $textData = $startOfDocument . $title . $authors . $affiliations . $abstractContent . $references . $endOfDocument;


            $filename = $folder . "/abstract.tex";
            $created = file_put_contents($filename, $textData);
            if ($created === false) {
                echo 'Export failed::failed to create abstract.tex::end';
                error_log($filename . " creation failed");
            }
            $abcd = shell_exec('/bin/pdflatex -interaction=nonstopmode --output-directory="' . $folder . '" "' . $folder . '/abstract.tex"');
            $_SESSION['generating'] = 0;
            $_SESSION['exists'] = 1;

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
else if (file_exists(__DIR__ . '/' . $folder . '/abstract.pdf')) {
    echo 'Export failed::' . $field_validity . '::end';
} else {
    echo 'Export failed::' . $field_validity . '::end';
}
?>