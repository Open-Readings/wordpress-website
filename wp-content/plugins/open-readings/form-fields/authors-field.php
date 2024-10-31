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
					<input type="text" class="author-width form-padding elementor-field" value="" name="name[]" placeholder="(e.g. John Smith)">
					<input type="text" class="narrow form-padding elementor-field" name="aff_ref[]" value="" placeholder="(e.g. 1,2)">
					<label class="text-like-elementor elementor-field"> Corresponding author </label> <input style="margin: 5px;" class="contact-author" type="radio" name="contact_author" value="1" checked><input id="email-author" class="form-padding elementor-field" style="display:inline;" value="" type="text" name="email-author" placeholder="john.smith@example.edu">
				</div>
			</div>
			<button type="button" class="auth-add" >Add</button>
			<button type="button" class="auth-rem">Remove</button><br>
		</div>';
	}
}