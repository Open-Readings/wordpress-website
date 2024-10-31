<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Elementor_Image_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

	public $depended_scripts = ['image-field-js'];

	public $depended_styles = ['registration-widget-style'];

	public function get_type()
	{
		return 'image-field';
	}

	public function get_name()
	{
		return esc_html__('Image Field', 'elementor-form-image-field');
	}

	public function render($item, $item_index, $form)
	{
		$max_files = get_option('or_registration_max_images');
		$max_files = $max_files ? $max_files : 2;
		?>
		<div class="full">
			<input class="text-like-elementor" type="file" id="fileInput" multiple>
			<div class="flex-div">
				<button id="fileButton" data-field-id=" <?php echo $item['custom_id']; ?>" hidden>Upload File</button>
				<div class="loader" id="uploadLoader"></div>
				<script>
					const max_files = <?php echo $max_files; ?>;


				</script>
			</div>
			<p id="image-names" class="text-like-elementor" style="display:none"></p>

		</div>


		<?php

	}
}