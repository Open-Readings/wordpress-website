<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Plugin;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

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
        $form->add_render_attribute('input' . $item_index, 'placeholder', $item['country_placeholder']);
        if (!empty($item['country_default_value'])) {
            $item['field_value'] = $item['country_default_value'];
            $form->add_render_attribute('input' . $item_index, 'value', $item['country_default_value']);
        }
        ?> <input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
        <?php
        echo '
		<div id="country-wrapper">
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
            'country_placeholder' => [
                'name' => 'country_placeholder',
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
            'country_default_value' =>
                [
                    'name' => 'country_default_value',
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