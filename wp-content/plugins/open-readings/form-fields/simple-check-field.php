<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use ElementorPro\Plugin;

class Elementor_Simple_Check_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public function get_name()
    {
        return esc_html__('Simple Check Field', 'OR');
    }

    public function get_type()
    {
        return 'simple-check-field';
    }

    public function update_controls($widget)
    {
        $elementor = Plugin::elementor();

        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'check_text' => [
                'name' => 'check_text',
                'label' => esc_html__('Check Text', 'OR'),
                'type' => Controls_Manager::TEXTAREA,
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'checked_by_default_switch' => [
                'name' => 'checked_by_default_switch',
                'label' => esc_html__('Checked by Default', 'OR'),
                'type' => Controls_Manager::SWITCHER,
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'check_default_value' => [
                'name' => 'check_default_value',
                'label' => esc_html__('Default Value', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'dynamic' => [
                    'active' => true,
                ],
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
        $label = '';
        $form->add_render_attribute('input' . $item_index, 'class', 'elementor-acceptance-field');
        $form->add_render_attribute('input' . $item_index, 'type', 'checkbox', true);

        if (!empty($item['check_text'])) {
            $label = '<label for="' . $form->get_attribute_id($item) . '">' . $item['check_text'] . '</label>';
        }

        if (!empty($item['checked_by_default_switch'])) {
            $form->add_render_attribute('input' . $item_index, 'checked', 'checked');
        }

        if (!empty($item['check_default_value'])) {
            $form->add_render_attribute('input' . $item_index, 'checked', 'checked');
        }

        ?>
        <div class="elementor-field-subgroup">
            <span class="elementor-field-option">
            <div style="display: flex; align-items: flex-start;">
                <div>
                    <input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
                </div>
                <div style="margin-left: 8px;">
                    <?php // PHPCS - the variables $label is safe.
                        echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>
            </span>
        </div>
        <?php



    }


}

?>