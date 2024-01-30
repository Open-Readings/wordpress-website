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

    public $depended_scripts = ['latex-field-js', 'highlight-js', 'latex-min-js'];

    public $depended_styles = ['latex-field-style', 'highlight-style'];

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
            ini_set('session.gc_maxlifetime', 3600);
            session_start();
            $_SESSION['id'] = 1;
        }

        $folder = $_SESSION['file'];

        $data_to_pass = array(
            'folder' => $folder,
            // Use admin-ajax.php for AJAX requests
        );
        wp_localize_script('latex-field-js', 'folderAjax', $data_to_pass);
        add_action('elementor_pro/loaded', function () {
            add_action('elementor/frontend/before_enqueue_scripts', function () {
                wp_enqueue_script('highlight-js');
                wp_enqueue_style('highlight-default-style');
            });
        });

        echo '
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <div class="latex-flex full">
        <input class="hidden" value= "' . $folder . '" name="session_id"/>
        <div class="latex-half-div">   
                    <textarea id="textArea" spellcheck="false" class="text-like-elementor test-style" name="textArea" rows="20" cols="50" placeholder="' . $item['latex_placeholder'] . '" required>' . $item['latex_default_value'] . '</textarea>
                    <pre id="latexResult" class="pre-style"><code class="language-latex code-style" id="display-latex-code">
\begin{equation}
\int = abc
\end{equation}
            </code></pre>
                    <p class="text-like-elementor margin-absolute">Character Count: <span id="charCount">0</span></p>
                    <div class="flex-div">
                    <button type="button" class="form-padding" id="latexButton">Generate abstract </button>
                    <div class="loader" id="loader"></div>
                    </div>
                    <p id="errorMessage" style="display: none; color: red;"></p>
                   
        </div>
        <div class="latex-half-div">
            
            <pre class="latex-error" id="logContent"></pre>
            <iframe class="pdf-frame" id="abstract" src="' . content_url() . '/latex/abstract.pdf#toolbar=0' . '"></iframe>
        </div>
        </div>';
        ?>
        <script>

            var pre_element = document.getElementById("latexResult");
            var text_area = document.getElementById("textArea");

            // Store the original size of the text_area
            var originalSize = {
                width: text_area.offsetWidth,
                height: text_area.offsetHeight
            };

            // Create a new ResizeObserver instance
            var resizeObserver = new ResizeObserver(function (entries) {
                // Loop over the entries
                entries.forEach(function (entry) {
                    // If the size has not changed, do nothing
                    if (entry.contentRect.width === originalSize.width && entry.contentRect.height === originalSize.height) {
                        return;
                    }

                    // Get the computed style of the text_area
                    var style = window.getComputedStyle(text_area);

                    // Calculate the scrollbar width

                    var marginLeft = parseFloat(style.paddingLeft);
                    var marginRight = parseFloat(style.paddingRight);
                    var marginTop = parseFloat(style.paddingTop);
                    var marginBottom = parseFloat(style.paddingBottom);

                    // Update the size of pre_element to match text_area, excluding margins
                    pre_element.style.width = (entry.contentRect.width + marginLeft + marginRight + 2) + "px";
                    pre_element.style.height = (entry.contentRect.height + marginTop + marginBottom + 2) + "px";
                });
            });

            // Start observing the text_area element
            resizeObserver.observe(text_area);

        </script>


        <?php
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