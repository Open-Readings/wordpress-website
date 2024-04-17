<?php
error_reporting(0);

function check_abstract_fields()
{


    $title_length = 200;
    $field_group = [
        ['name', 'Author name', 200, '/[^\\p{L}\-.,;() ]/u'],
        ['aff_ref', 'Affiliation number', 200, '[0-9, ]*'],
        ['email-author', 'Corresponding author email', 100, ''],
        ['affiliation', 'Affiliation', 200, '/[^\\p{L}0-9 <>.,()\-&*":;!$]/u'],
        ['textArea', 'Abstract content', 3000, ''],
        ['references', 'Reference', 1000, '']
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
            if (mb_strlen($field) - substr_count($field, "\n") > $item[2]) {
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
    $title = json_encode($_POST['form_fields']['abstract_title'], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT);
    $title = str_replace('\/', '/', $title);
    $title = str_replace('"', '', $title);

    if (mb_strlen($title) > $title_length) {
        return "Title field input too long";
    } else if (preg_match('/[^\p{L}\p{N}, \\\\+=<>^;:(){}$*\-.\/]/u', $title)) {
        return "Title field: special characters not allowed in field. " . $title;
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
        ini_set('session.gc_maxlifetime', 14400);
        session_start();
        $_SESSION['id'] = 1;
    }

    if (!isset($_SESSION['generating'])) {
        $_SESSION['generating'] = 0;
    }

    if ($_SESSION['generating'] == 0) {

        $_SESSION['generating'] = 1;
        $folder = $_SESSION['file'];
        copy('orstylet.sty', $folder . '/orstylet.sty');
        

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            

            $i = 1;
            $authors = '';
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

            $affiliations = '';
            $i = 1;
            foreach ($_POST['affiliation'] as $aff) {
                $affiliations = $affiliations . '\address{$^{' . $i . '}$' . $aff . '}
            ';
                $i++;
            }
            $affiliations = $affiliations . '\rightaddress{' . $_POST['email-author'] . '}';

        if(isset($_POST['references'])){
            $references = '
            \vfill    
            \begin{thebibliography}{}
            ';
            $i = 1;
            foreach ($_POST['references'] as $ref) {
               $references .= '\bibitem{' . $i . '} ' . $ref . '
               ';
               $i++;
            }
            $references .= '\end{thebibliography}
            ';
            } else{
                $references = '';
            }



            $titleField = $_POST['form_fields']['abstract_title'];
            //$titleField = str_replace('"', '', $title);

            // Add missing </sup> tags
            $titleField = fixUnclosedTags($titleField, '<sup>', '</sup>');

            // Add missing </sub> tags
            $titleField = fixUnclosedTags($titleField, '<sub>', '</sub>');


            $sup_starting_tag = '<sup>';
            $sub_starting_tag = '<sub>';
            $sub_ending_tag = '</sub>';
            $sup_ending_tag = '</sup>';
            $layers = 0;
            $is_in_math_mode = false;

            for ($i = 0; $i < mb_strlen($titleField); $i++) {
                if (mb_substr($titleField, $i, mb_strlen($sup_starting_tag)) == $sup_starting_tag) {
                    $sup_starting_tag_index = $i;
                    $layers++;
                    if ($layers == 1) {
                        $titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '$^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
                    } else {
                        //replace <sup> with $^{
                        $titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
                    }
                    $i -= mb_strlen($sup_starting_tag);
                }
                if (mb_substr($titleField, $i, mb_strlen($sub_starting_tag)) == $sub_starting_tag) {
                    $sub_starting_tag_index = $i;
                    $layers++;
                    if ($layers == 1) {
                        $titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '$_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
                    } else {
                        //replace <sub> with $_{
                        $titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
                    }
                    $i -= mb_strlen($sup_starting_tag);

                }

                if (mb_substr($titleField, $i, mb_strlen($sub_ending_tag)) == $sub_ending_tag) {
                    $sub_ending_tag_index = $i;
                    $layers--;
                    if ($layers == 0) {
                        //replace </sub> with }$
                        $titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}$' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
                    } else {
                        //replace </sub> with }$
                        $titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
                    }
                    //replace </sub> with }$
                    $i -= mb_strlen($sup_starting_tag);
                }
                if (mb_substr($titleField, $i, mb_strlen($sup_ending_tag)) == $sup_ending_tag) {
                    $sup_ending_tag_index = $i;
                    $layers--;
                    if ($layers == 0) {
                        //replace </sup> with }$
                        $titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
                    } else {
                        //replace </sup> with }$
                        $titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
                    }
                    $i -= mb_strlen($sup_starting_tag);
                }

            }
            $titleField = str_replace('&nbsp;', ' ', $titleField);

            //find fist <sup> or <sub> tag


            $abstractContent = $_POST["textArea"];

            $templateFilePath = '../plugins/open-readings/evaluation/admin/template.txt';
            $templateContent = file_get_contents($templateFilePath);

            $filename = $folder . "/abstract.tex";
            // Define your variables
            // Add more variables as needed
        
            // Create an associative array of placeholders and their corresponding values
            $replacements = array(
                '${title}' => $titleField,
                '${authors}' => $authors,
                '${affiliations}' => $affiliations,
                '${content}' => $abstractContent,
                '${references}' => $references
        
                // Add more placeholders and values as needed
            );
        
            // Replace placeholders in the template content
            $templateContent = str_replace(array_keys($replacements), array_values($replacements), $templateContent);
            $created = file_put_contents($filename, $templateContent);

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
