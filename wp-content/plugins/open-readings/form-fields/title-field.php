<?php

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Plugin;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;


// add autoloader
// require_once OR_PLUGIN_DIR . 'vendor/autoload.php';


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
		$form->add_render_attribute('input' . $item_index, 'value', '');
		$form->add_render_attribute('div' . $item_index, 'contenteditable', 'true');
		$form->add_render_attribute('div' . $item_index, 'id', 'presentation_title_div');
		$form->add_render_attribute('div' . $item_index, 'class', 'elementor-field elementor-size-sm elementor-field-textual title-field-pad');
		$field_id = $form->get_attribute_id($item);

		if ($item['allow_script']) {
			?>
			<span style="font-size: 0.8em;" class="text-like-elementor full">To type in superscript or subscript use ^ and _
				accordingly in the field (use
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
				var allowed_regex = "<?php echo $item['regex'] ?>"
				if (allowed_regex == "") {
					allowed_regex = "\\p{L}\\p{N}\\s&_^<>\\-\\\\(){}$=+;\\/";
				}
				var allowed_sub_regex = "<?php echo $item['sub_regex'] ?>"
				if (allowed_sub_regex == "") {
					allowed_sub_regex = "A-Za-z0-9+$\\-=()";
				}
				var regex_string = '<(?!sub\\s*\\/?)(?!sup\\s*\\/?)(?!/sup\\s*\\/?)(?!/sub\\s*\\/?)[^>]+>';
				var regex = new RegExp(regex_string, "g");
				var html = value.replace(regex, '');
				//console.log(new RegExp("[\^" + allowed_regex + "]", "gu"));
				html = html.replace(new RegExp("[\^" + allowed_regex + "]", "gu"), '');
				//var html = html.replace(/[^\p{L}\p{N}\s&^_<>\- ()=+;\/]/gu, "");
				//console.log(html);
				//allowed_regex = allowed_regex.replace(/[\^_&]/g, '');
				var sup_regex = new RegExp("\\^([" + allowed_sub_regex + "]+)", "g");
				var sub_regex = new RegExp("_([" + allowed_sub_regex + "]+)", "g");
				html = html.replace(sup_regex, '<sup>$1</sup>&nbsp;').replace(sub_regex, '<sub>$1</sub>&nbsp;');

				html = html.replace('&nbsp;</sup>', '</sup>');
				html = html.replace('</sup>&nbsp;', '</sup>');
				html = html.replace('&nbsp;</sub>', '</sub>');
				html = html.replace('</sub>&nbsp;', '</sub>');
				html = html.replace('</sup>', '</sup>&nbsp;');
				html = html.replace('</sub>', '</sub>&nbsp;');

				
				document.getElementById("presentation_title_div").innerHTML = html;
				document.getElementById(field_id).value = String(html).replace(/\u200B/g, '').replace(/&nbsp;/g, ' ');

				//trim if more than 500 chars
				if (html.length > 250) {
					document.getElementById("presentation_title_div").innerHTML = html.substring(0, 250);
					document.getElementById(field_id).value = String(html).replace('&nbsp;', ' ').substring(0, 250);
				}


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
					tags[i].onkeydown = function (event) {
						if (this.innerHTML != this.data_orig) {
							if (event.key === ' ' || event.key === '^' || event.key === '_'){
								<?php if ($item['allow_script']): ?>
									// console.log(this.innerHTML);
									trim_title(this.innerHTML);
								
								<?php else: ?>
									var field_id = "<?= $field_id ?>";
									document.getElementById(field_id).value = this.innerHTML;

								<?php endif; ?>
								delete this.data_orig;
								const range = document.createRange();
								range.selectNodeContents(this);
								range.collapse(false);

								const selection = window.getSelection();
								selection.removeAllRanges();
								selection.addRange(range);
							}
						}
					}
					tags[i].onblur = function(){
						if (this.innerHTML == "") {
							this.innerHTML = default_value;
						} else {
							trim_title(this.innerHTML);
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
		$titleField = $field['raw_value'];
		$sup_starting_tag = '<sup>';
		$sub_starting_tag = '<sub>';
		$sub_ending_tag = '</sub>';
		$sup_ending_tag = '</sup>';
		$layers = 0;
		$is_in_math_mode = false;

		for ($i = 0; $i < mb_strlen($titleField); $i++) {
			if (mb_substr($titleField, $i, mb_strlen($sup_starting_tag)) == $sup_starting_tag) {
				$sup_starting_tag_index = $i;
				$layers++;
				if ($layers == 1) {
					$titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '$^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
				} else {
					//replace <sup> with $^{
					$titleField = mb_substr($titleField, 0, $sup_starting_tag_index) . '^{' . mb_substr($titleField, $sup_starting_tag_index + mb_strlen($sup_starting_tag));
				}
				$i -= mb_strlen($sup_starting_tag);
			}
			if (mb_substr($titleField, $i, mb_strlen($sub_starting_tag)) == $sub_starting_tag) {
				$sub_starting_tag_index = $i;
				$layers++;
				if ($layers == 1) {
					$titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '$_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
				} else {
					//replace <sub> with $_{
					$titleField = mb_substr($titleField, 0, $sub_starting_tag_index) . '_{' . mb_substr($titleField, $sub_starting_tag_index + mb_strlen($sub_starting_tag));
				}
				$i -= mb_strlen($sup_starting_tag);

			}

			if (mb_substr($titleField, $i, mb_strlen($sub_ending_tag)) == $sub_ending_tag) {
				$sub_ending_tag_index = $i;
				$layers--;
				if ($layers == 0) {
					//replace </sub> with }$
					$titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}$' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
				} else {
					//replace </sub> with }$
					$titleField = mb_substr($titleField, 0, $sub_ending_tag_index) . '}' . mb_substr($titleField, $sub_ending_tag_index + mb_strlen($sub_ending_tag));
				}
				//replace </sub> with }$
				$i -= mb_strlen($sup_starting_tag);
			}
			if (mb_substr($titleField, $i, mb_strlen($sup_ending_tag)) == $sup_ending_tag) {
				$sup_ending_tag_index = $i;
				$layers--;
				if ($layers == 0) {
					//replace </sup> with }$
					$titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
				} else {
					//replace </sup> with }$
					$titleField = mb_substr($titleField, 0, $sup_ending_tag_index) . '}$' . mb_substr($titleField, $sup_ending_tag_index + mb_strlen($sup_ending_tag));
				}
				$i -= mb_strlen($sup_starting_tag);
			}

		}
		$titleField = str_replace('&nbsp;', ' ', $titleField);




		// $html = (new \Pandoc\Pandoc)
		// 	->from('latex')
		// 	->input($titleField)
		// 	->to('html')
		// 	->run();

		$html = "x";


		//find the first <p> tag
		$first_p_tag_index = strpos($html, '<p>');
		//find the last </p> tag
		$last_p_tag_index = strrpos($html, '</p>');

		$html = substr($html, $first_p_tag_index + 3, $last_p_tag_index - $first_p_tag_index - 3);


		$display_title = $html;





		$record->update_field($field['id'], 'value', $display_title);

		$raw_value = $field['raw_value'];
		$raw_value = str_replace('\\\\\\\\', '\\', $raw_value);



		$record->update_field($field['id'], 'raw_value', $raw_value);
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
			'regex' =>
				[
					'name' => 'regex',
					'label' => esc_html__('Regex', 'elementor-pro'),
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
					'tabs_wrapper' => 'form_fields_tabs'
				],
			'sub_regex' =>
				[
					'name' => 'sub_regex',
					'label' => esc_html__('Sub/Sup Regex', 'elementor-pro'),
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
					'tabs_wrapper' => 'form_fields_tabs'
				],
		];

		$control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
		$widget->update_control('form_fields', $control_data);
	}
}


?>