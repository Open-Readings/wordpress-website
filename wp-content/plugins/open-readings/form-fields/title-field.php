<?php

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Plugin;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class TitleField extends ElementorPro\Modules\Forms\Fields\Field_Base
{
	public $depended_scripts = ['title-field-js'];

	public $depended_styles = ['registration-widget-style'];
	public function get_type()
	{
		return 'title_field';
	}

	public function get_name()
	{
		return esc_html__('Title field', 'OR');
	}

	public function render($item, $item_index, $form)
	{
		$form->add_render_attribute('input' . $item_index, 'class', 'hidden');
		$form->add_render_attribute('div' . $item_index, 'contenteditable', 'true');
		$form->add_render_attribute('div' . $item_index, 'id', 'presentation_title_div');
		$form->add_render_attribute('div' . $item_index, 'class', 'elementor-field elementor-size-sm elementor-field-textual');
		$field_id = $form->get_attribute_id($item);

		if ($item['allow_script']) {
			?>
			<span style="font-size: 0.8em;" class="text-like-elementor full">To type in superscript or subscript use ^ and _ accordingly in the field (use
				whitespaces to escape them)</span>
			<?php

		}
		if (!empty($item['title_default_value']))
			$item['field_value'] = $item['title_default_value'];

		$default_value = $item['field_value'];
		if (empty($default_value)) {
			$default_value = $item['title_placeholder'];
		}

		?>

		<div <?php $form->print_render_attribute_string('div' . $item_index) ?>>
			<?php echo $default_value ?>
		</div>
		<input <?php $form->print_render_attribute_string('input' . $item_index); ?>>
		<script>
			function trim_title(value) {
				var field_id = "<?php echo $field_id ?>";
				var regex_string = '<(?!sub\\s*\\/?)(?!sup\\s*\\/?)(?!/sup\\s*\\/?)(?!/sub\\s*\\/?)[^>]+>';
				var regex = new RegExp(regex_string, "g");
				var html = value.replace(regex, '');
				html = html.replace('^', '<sup>');
				html = html.replace('&nbsp;</sup>', '</sup>&nbsp;');
				html = html.replace('_', '<sub>');
				html = html.replace('&nbsp;</sub>', '</sub>&nbsp;');
				document.getElementById("presentation_title_div").innerHTML = html;
				document.getElementById(field_id).value = String(html).replace('&nbsp;', ' ');




			};

			function fix_onChange_editable_elements() {
				var default_value = "<?php echo $item['title_placeholder']; ?>";
				var tags = document.querySelectorAll('[contenteditable=true]');//(requires FF 3.1+, Safari 3.1+, IE8+)
				for (var i = tags.length - 1; i >= 0; i--) if (typeof (tags[i].onblur) != 'function') {
					tags[i].onfocus = function () {
						if (this.innerHTML.trim() == default_value) {

							this.innerHTML = "";
						}
						this.data_orig = this.innerHTML;
					};
					tags[i].onblur = function () {
						if (this.innerHTML != this.data_orig) {
							<?php if ($item['allow_script']): ?>
								// console.log(this.innerHTML);
								trim_title(this.innerHTML);

							<?php else: ?>
								var field_id = "<?= $field_id ?>";
								document.getElementById(field_id).value = this.innerHTML;

							<?php endif; ?>
							delete this.data_orig;
						}
						if (this.innerHTML == "") {
							this.innerHTML = default_value;
						}
					}
				};
			}

			fix_onChange_editable_elements();

		</script>
		<?php
	}
	public function process_field($field, Form_Record $record, Ajax_Handler $ajax_handler)
	{

		$record->update_field($field['id'], 'value', $field['raw_value']);
		$record->update_field($field['id'], 'raw_value', $field['raw_value']);
	}

	public function update_controls($widget)
	{
		$elementor = Plugin::elementor();

		$control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

		if (is_wp_error($control_data)) {
			return;
		}

		$field_controls = [
			'allow_script' => [
				'name' => 'allow_script',
				'label' => esc_html__('allow sub/sup script', 'OR'),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab' => 'content',
				'inner_tab' => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',

			],
			'title_placeholder' => [
				'name' => 'title_placeholder',
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
			'title_default_value' =>
				[
					'name' => 'title_default_value',
					'label' => esc_html__('Default Value', 'elementor-pro'),
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


?>