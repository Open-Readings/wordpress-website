<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Plugin;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

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
        }
        $fdsokal = is_dir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file']);
        if (!is_dir(WP_CONTENT_DIR . '/latex/' . $_SESSION['file'])) {
            shell_exec('/bin/mkdir "' . WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '"');
            shell_exec('/bin/mkdir "' . WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '/images"');
            shell_exec('/bin/cp "' . WP_CONTENT_DIR . '/latex/abstract.pdf" "' . WP_CONTENT_DIR . '/latex/' . $_SESSION['file'] . '"');
        }


        $folder = $_SESSION['file'];

        $data_to_pass = array(
            'folder' => $folder,
            // Use admin-ajax.php for AJAX requests
        );
        wp_localize_script('latex-field-js', 'folderAjax', $data_to_pass);

        echo '
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <div class="latex-flex full">
        <input class="hidden" value= "' . $_SESSION['file'] . '" name="session_id"/>
        <div class="latex-half-div">   
                    <textarea id="textArea" class="text-like-elementor" name="textArea" rows="20" cols="50" placeholder="' . $item['latex_placeholder'] . '" required>' . $item['latex_default_value'] . '</textarea>
                    <p class="text-like-elementor">Character Count: <span id="charCount">0</span></p>
                    <div class="flex-div">
                    <button type="button" id="latexButton">Generate abstract </button>
                    <div class="loader" id="loader"></div>
                    </div>
                    <p id="errorMessage" style="display: none; color: red;"></p>
                
        </div>
        <div class="latex-half-div">
            <pre class="latex-error" id="logContent"></pre>
            <iframe class="pdf-frame" id="abstract" src="' . content_url() . '/latex/abstract.pdf#toolbar=0' . '"></iframe>
        </div>
        </div>';
    }

    public function update_controls($widget)
    {
        $elementor = Plugin::elementor();


        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'latex_placeholder' => [
                'name' => 'latex_placeholder',
                'label' => esc_html__('Placeholder', 'OR'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'default' => esc_html__('', 'OR'),
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'latex_default_value' =>
                [
                    'name' => 'latex_default_value',
                    'label' => esc_html__('Default Value', 'OR'),
                    'type' => Controls_Manager::TEXT,
                    'condition' => [
                        'field_type' => $this->get_type(),
                    ],
                    'dynamic' => [
                        'active' => true,
                    ],
                    'default' => esc_html__('', 'OR'),
                    'tab' => 'advanced',
                    'inner_tab' => 'form_fields_advanced_tab',
                    'tabs_wrapper' => 'form_fields_tabs',
                ],
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

}