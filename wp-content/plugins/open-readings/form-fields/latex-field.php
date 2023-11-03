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
        if(!isset($_SESSION['id'])) {
            session_start();
            $_SESSION['id'] = 1;
        }
        
        if(!isset($_SESSION['file'])) {
            $timestamp = time();
            $_SESSION['file'] = $timestamp . substr(md5(mt_rand()), 0, 8);
        }
        $fdsokal = is_dir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file']);
        if(!is_dir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file'])) {
            shell_exec('/bin/mkdir "' . WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '"');
            shell_exec('/bin/mkdir "' . WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '/images"');
            shell_exec('/bin/cp "' . WP_CONTENT_DIR . '/latex/3.pdf" "' . WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '"');
        }


        $folder = $_SESSION['file'];

        $data_to_pass = array(
            'folder' => $folder, // Use admin-ajax.php for AJAX requests
          );
          wp_localize_script( 'latex-field-js', 'folderAjax', $data_to_pass );

        echo '
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <div class="latex-flex full">
        <div class="latex-half-div">   
                    <textarea id="textArea" name="textArea" rows="20" cols="50"></textarea>
                    <p>Character Count: <span id="charCount">0</span></p>
                     <br><br>
                    
                    <button type="button" id="latexButton">Generate abstract</button>
                
        </div>
        <div class="latex-half-div">
            <iframe id="abstract" src="' . content_url() . '/latex/' . $_SESSION['file'] . "/3.pdf#toolbar=0" . '" height="1200"></iframe>
            <pre class="scroll" id="logContent"></pre>
        </div>
        </div>';
    }

}