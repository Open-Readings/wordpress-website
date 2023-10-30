<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Country_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public $depended_scripts = ['country-field-js'];

    public $depended_styles = ['registration-widget-style'];

    public function get_type()
    {
        return 'country-field';
    }

    public function get_name()
    {
        return esc_html__('Country Field', 'elementor-form-country-field');
    }

    public function render($item, $item_index, $form)
    {
        global $wpdb;
        $results = $wpdb->get_results('SELECT * FROM countries');
        $countries = array();
        foreach ($results as $result) {
            $countries[$result->id] = $result->name;
        }

        wp_enqueue_script('country-field-js');
        wp_localize_script(
            'country-field-js',
            'registration_ajax',
            array(
                'items' => $countries,
                'custom_id' => $item['custom_id']
            )
        );
        $form->add_render_attribute('input' . $item_index, 'class', 'elementor-field-textual country-input');

        ?> <input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
        <?php
        echo '
		<div id="country-wrapper">
		</div>';
    }

}