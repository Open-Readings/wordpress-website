<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Elementor_Authors_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

	public $depended_scripts = ['authors-field-js'];

	public $depended_styles = ['registration-widget-style'];

	public function get_type()
	{
		return 'authors-field';
	}

	public function get_name()
	{
		return esc_html__('Authors Field', 'elementor-form-authors-field');
	}



	public function render($item, $item_index, $form)
	{
		echo '
        <div class="full">
			<div id="authList">
				<div>
					<input type="text" pattern="^[^&%\$\\#^_\{\}~]*$" class="author-width form-padding" name="name[]" placeholder="(e.g. John Smith)" required>
					<input type="text" pattern="[0-9, ]*" class="narrow form-padding" name="aff_ref[]" placeholder="(e.g. 1,2)" required>
					<label class="text-like-elementor"> Corresponding author </label> <input style="margin: 5px;" class="contact-author" type="radio" name="contact_author" value="1" checked><input id="email-author" style="display:inline;" type="email" name="email-author" placeholder="john.smith@example.edu" required>
				</div>
			</div>
			<button type="button" class="auth-add" >Add</button>
			<button type="button" class="auth-rem">Remove</button><br>
		</div>';
	}
}