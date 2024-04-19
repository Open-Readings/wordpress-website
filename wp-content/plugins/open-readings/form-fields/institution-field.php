<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Plugin;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

class Elementor_Institution_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public $depended_scripts = ['institution-field-js', 'institution-list-js'];

    public $depended_styles = ['registration-widget-style'];

    public function get_type()
    {
        return 'institution-field';
    }

    public function get_name()
    {
        return esc_html__('Institution Field', 'OR');
    }

    public function update_controls($widget)
    {
        $elementor = Plugin::elementor();


        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'institution_placeholder' => [
                'name' => 'institution_placeholder',
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
            'institution_default_value' =>
                [
                    'name' => 'institution_default_value',
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


    public function render($item, $item_index, $form)
    {
        wp_enqueue_script('institutions-list-js');
        wp_enqueue_script('institution-field-js');
        wp_localize_script(
            'institution-field-js',
            'institution_field_ajax',
            array(
                'institution_id' => $item['custom_id']
            )
        );

        $form->add_render_attribute('input' . $item_index, 'class', 'elementor-field-textual affiliation-input');
        $form->add_render_attribute('input' . $item_index, 'placeholder', $item['institution_placeholder']);
        if (!empty($item['institution_default_value'])) {
            $item['field_value'] = $item['institution_default_value'];
            $form->add_render_attribute('input' . $item_index, 'value', $item['institution_default_value']);
        }
        ?> <input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
        <?php
        echo '
        <div id="institution-wrapper">
        </div>';
    }

}