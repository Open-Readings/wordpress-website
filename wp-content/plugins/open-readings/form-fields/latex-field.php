<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Latex_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public $depended_scripts = ['latex-field-js'];

    public $depended_styles = ['latex-field-style'];

    public function get_type()
    {
        return 'latex-field';
    }

    public function get_name()
    {
        return esc_html__('Latex Field', 'elementor-form-latex-field');
    }

    public function render($item, $item_index, $form)
    {
        if (!isset($_SESSION['id'])) {
            session_start();
            $_SESSION['id'] = 1;

        }

        if (!isset($_SESSION['file'])) {
            $timestamp = time();
            $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
            shell_exec('/bin/mkdir latex/' . $_SESSION['file']);
            shell_exec('/bin/mkdir latex/' . $_SESSION['file'] . "/images");
            shell_exec('/bin/cp latex/3.pdf ' . "latex/" . $_SESSION['file']);
        }

        $folder = $_SESSION['file'];

        echo '<div class="latex-flex">
        <div class="latex-half-div">
                    

                    <label for="textArea">Abstract content</label> <br>
                    <textarea id="textArea" name="textArea" rows="20" cols="50"></textarea> <br><br>
                    
                    <button type="button" id="latexButton">Export to File</button>
                
        </div>
        <div class="latex-half-div">
            <iframe id="abstract" src="' . "/latex/" . $_SESSION['file'] . "/3.pdf#toolbar=0" . '" height="1200"></iframe>
        </div>
    </div>';
    }

}