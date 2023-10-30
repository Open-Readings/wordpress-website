<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_University_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public $depended_scripts = ['university-field-js'];

    public $depended_styles = ['registration-widget-style'];

    public function get_type()
    {
        return 'university-field';
    }

    public function get_name()
    {
        return esc_html__('University Field', 'elementor-form-university-field');
    }

    public function render($item, $item_index, $form)
    {
        global $wpdb;

        $results = $wpdb->get_results('SELECT * FROM linkedin_universities');
        $universities = array();
        foreach ($results as $result) {
            $universities[$result->id] = $result->name;
        }
        wp_enqueue_script('university-field-js');
        wp_localize_script(
            'university-field-js',
            'registration_ajax',
            array(
                'uni_items' => $universities,
                'uni_id' => $item['custom_id']
            )
        );

        $form->add_render_attribute('input' . $item_index, 'class', 'elementor-field-textual affiliation-input');
        ?> <input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
        <?php
        echo '
        <div id="university-wrapper">
        </div>';
    }

}