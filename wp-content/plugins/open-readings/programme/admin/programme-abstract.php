<?php

use OpenReadings\Registration\ORLatexExport;
use OpenReadings\Registration\OpenReadingsRegistration;
use OpenReadings\Registration\PersonData;
use OpenReadings\Registration\PresentationData;
use OpenReadings\Registration\RegistrationData;

if(isset($_POST['save-page-template'])){
    update_option('abstract-book-page-template', $_POST['page-template']);
}

if(isset($_POST['save-abstract-preamble'])){
    update_option('abstract-book-preamble', $_POST['preamble-template']);
}



?>
<h1>Abstract book generation</h1>




<form method="POST">
    <label>Enter hash ids separated by newline</label><br>
    <textarea cols=60 rows="20" name="abstract-hash-ids"></textarea><br>
    <input type="checkbox" id="figures" name="download-figures"><label for="figures">Download figures and style</label><br>
    <button class="button-primary" name="download-abstracts">Abstract download</button>
</form><br>

<form method="POST">
    <label>Latex page template</label><br>
    <textarea rows=20 cols=60 name="page-template"><?=wp_unslash(get_option('abstract-book-page-template'))?></textarea><br>
    <button class="button-primary" name="save-page-template">Save</button>
</form><br>

<form method="POST">
    <label>Latex preamble</label><br>
    <textarea rows=20 cols=60 name="preamble-template"><?=wp_unslash(get_option('abstract-book-preamble'))?></textarea><br>
    <button class="button-primary" name="save-abstract-preamble">Save</button>
</form><br>

