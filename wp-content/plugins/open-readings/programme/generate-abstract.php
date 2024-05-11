<?php


function download_abstract(){
    if (!isset($_GET['page']) or $_GET['page'] != 'or_programme_abstract') 
        return;

    if(!isset($_POST['download-abstracts']))
        return;
    $abstract_fields = get_abstract_data();
    // var_dump($abstract_fields);

    global $wpdb;
    $results = $wpdb->get_results("SELECT person_hash_id FROM wp_or_registration_presentations");
    $all_hash_ids = array();
    foreach ($results as $result){
        $all_hash_ids[] = $result->person_hash_id;
    }

    if(isset($_POST['abstract-hash-ids']) and trim($_POST['abstract-hash-ids']) != ''){
        $hash_ids = explode("\n", $_POST['abstract-hash-ids']);
        foreach($hash_ids as $key => $id){
            $hash_ids[$key] = trim($id);
        }
        foreach($hash_ids as $key => $id){
            if(!in_array($id, $all_hash_ids))
                unset($hash_ids[$key]);
        }
    } else {
        $hash_ids = $all_hash_ids;
    }


    $zip = new ZipArchive();
    $zip_file = 'abstract_book.zip';

    $data = generate_abstract_book($hash_ids);
    $dataFile = 'abstract.tex';

    file_put_contents($dataFile, $data);


    if ($zip->open($zip_file, ZipArchive::CREATE) !== true) {
        return false;
    }

    $zip->addFile($dataFile, $dataFile);
    if(isset($_POST['download-figures'])){
        $zip->addEmptyDir('figures');
        $zip->addFile(__DIR__ . '/orstylet.sty', 'orstylet.sty');
        
        foreach($hash_ids as $id){
            $folder = $abstract_fields[$id]['session_id'];
            $images = $abstract_fields[$id]['images'];
            $path = ABSPATH . 'wp-content/latex/' . $folder;
            foreach($images as $image){
                $file_path = $path . '/images/' . $image;
                $zip->addFile($file_path, "figures/{$folder}/{$image}");
            }
        }
    }
    
    
    $zip->close();
    // Set headers to initiate download
    header('Content-disposition: attachment; filename=' . $zip_file);
    header('Content-Type: application/zip');
    readfile("$zip_file");
    unlink($zip_file);
    // Exit to prevent any further output
    exit;

}

function generate_abstract_book($hash_ids){
    $abstract_fields = get_abstract_data();
    $content = wp_unslash(get_option('abstract-book-preamble'));

    foreach($hash_ids as $hash){
        $content .= get_abstract_page($hash, $abstract_fields);
    }

    $content .= '
    \end{document}';


    return $content;
}

function get_abstract_page($hash_id, $abstract_fields){
    $title = $abstract_fields[$hash_id]['display_title'];
    $title = abstract_format_title($title);

    $i = 1;
    $authors_array = $abstract_fields[$hash_id]['authors'];
    $authors = '';
    $author_list = '';
    foreach ($authors_array as $auth) {
        $name = $auth[0];
        $author_list = $author_list . $name;
        $aff_ref = $auth[1];
        //replace everything that is not a digit or ,

        if (isset($auth[2])){
            $authors = $authors . '\underline{' . $name . '}$^{' . $aff_ref . '}$';
            $author_email = $auth[2];
        } else
            $authors = $authors . $name . '$^{' . $aff_ref . '}$';

        if ($i < count($authors_array)){
            $authors = $authors . ', ';
            $author_list = $author_list . ', ';
        }
        $i++;
    }

    $affiliations_array = $abstract_fields[$hash_id]['affiliations'];
    $affiliations = '';
    $i = 1;
    foreach ($affiliations_array as $aff) {
        $affiliations = $affiliations . '\address{$^{' . $i . '}$' . $aff . '}
    ';
        $i++;
    }
    $affiliations = $affiliations . '\rightaddress{\href{' . $author_email . '}{' . $author_email . '}
}';

    $reference_array = $abstract_fields[$hash_id]['references'];

    if(is_array($reference_array) and count($reference_array) > 0){
        // var_dump($reference_array);
        $references = '
    \vfill    
    \begin{thebibliography}{}
        ';
        $i = 1;
        foreach ($reference_array as $ref) {
           $references .= '\bibitem{' . $i . '} ' . $ref . '
           ';
           $i++;
        }
        $references .= '
        \end{thebibliography}
        ';
       
        } else{
            $references = '';
        }

    $abstractContent = $abstract_fields[$hash_id]['content'];

    $pattern = '/\\\\includegraphics\[(.*?)\]\{(.*?)\}/u';

    // Replacement string
    $path = "figures/{$abstract_fields[$hash_id]['session_id']}";
    $replacement = '\\includegraphics[$1]{' . $path . '/$2}';

    // Perform the replacement
    $abstractContent = preg_replace($pattern, $replacement, $abstractContent);

    $templateContent = wp_unslash(get_option('abstract-book-page-template'));

    $replacements = array(
        '${title}' => $title,
        '${authors}' => $authors,
        '${affiliations}' => $affiliations,
        '${content}' => $abstractContent,
        '${references}' => $references,
        '${author_list}' => $author_list

        // Add more placeholders and values as needed
    );

    // Replace placeholders in the template content
    $templateContent = str_replace(array_keys($replacements), array_values($replacements), $templateContent);


    return $templateContent;
}

function get_abstract_data(){
    global $wpdb;

    $results = $wpdb->get_results("SELECT person_hash_id, display_title, authors, affiliations, `references`, content, images, session_id FROM wp_or_registration_presentations");
   
    $abstract_fields = array();
    foreach($results as $result){
        $abstract_fields[$result->person_hash_id]['display_title'] = $result->display_title;
        $abstract_fields[$result->person_hash_id]['authors'] = json_decode($result->authors);
        $abstract_fields[$result->person_hash_id]['affiliations'] = json_decode($result->affiliations);
        $abstract_fields[$result->person_hash_id]['references'] = wp_unslash(json_decode($result->references));
        $abstract_fields[$result->person_hash_id]['content'] = wp_unslash($result->content);
        $abstract_fields[$result->person_hash_id]['images'] = json_decode($result->images, ARRAY_A);
        $abstract_fields[$result->person_hash_id]['session_id'] = $result->session_id;
    }
    return $abstract_fields;
}

function abstract_format_title($title){
    $title = fixUnclosedTags($title, '<sup>', '</sup>');

    // Add missing </sub> tags
    $title = fixUnclosedTags($title, '<sub>', '</sub>');

    $sup_starting_tag = '<sup>';
    $sub_starting_tag = '<sub>';
    $sub_ending_tag = '</sub>';
    $sup_ending_tag = '</sup>';
    $layers = 0;
    $is_in_math_mode = false;

    for ($i = 0; $i < mb_strlen($title); $i++) {
        if (mb_substr($title, $i, mb_strlen($sup_starting_tag)) == $sup_starting_tag) {
            $sup_starting_tag_index = $i;
            $layers++;
            if ($layers == 1) {
                $title = mb_substr($title, 0, $sup_starting_tag_index) . '$^{' . mb_substr($title, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
            } else {
                //replace <sup> with $^{
                $title = mb_substr($title, 0, $sup_starting_tag_index) . '^{' . mb_substr($title, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
            }
            $i -= mb_strlen($sup_starting_tag);
        }
        if (mb_substr($title, $i, mb_strlen($sub_starting_tag)) == $sub_starting_tag) {
            $sub_starting_tag_index = $i;
            $layers++;
            if ($layers == 1) {
                $title = mb_substr($title, 0, $sub_starting_tag_index) . '$_{' . mb_substr($title, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
            } else {
                //replace <sub> with $_{
                $title = mb_substr($title, 0, $sub_starting_tag_index) . '_{' . mb_substr($title, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
            }
            $i -= mb_strlen($sup_starting_tag);

        }

        if (mb_substr($title, $i, mb_strlen($sub_ending_tag)) == $sub_ending_tag) {
            $sub_ending_tag_index = $i;
            $layers--;
            if ($layers == 0) {
                //replace </sub> with }$
                $title = mb_substr($title, 0, $sub_ending_tag_index) . '}$' . mb_substr($title, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
            } else {
                //replace </sub> with }$
                $title = mb_substr($title, 0, $sub_ending_tag_index) . '}' . mb_substr($title, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
            }
            //replace </sub> with }$
            $i -= mb_strlen($sup_starting_tag);
        }
        if (mb_substr($title, $i, mb_strlen($sup_ending_tag)) == $sup_ending_tag) {
            $sup_ending_tag_index = $i;
            $layers--;
            if ($layers == 0) {
                //replace </sup> with }$
                $title = mb_substr($title, 0, $sup_ending_tag_index) . '}$' . mb_substr($title, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
            } else {
                //replace </sup> with }$
                $title = mb_substr($title, 0, $sup_ending_tag_index) . '}$' . mb_substr($title, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
            }
            $i -= mb_strlen($sup_starting_tag);
        }

    }

    
    
    $title = str_replace('&nbsp;', ' ', $title);

    return $title;

    
}
