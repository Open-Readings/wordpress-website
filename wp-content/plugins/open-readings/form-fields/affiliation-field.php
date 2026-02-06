<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class Elementor_Affiliation_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

	public $depended_scripts = ['affiliation-field-js'];

	public $depended_styles = ['registration-widget-style'];

	public function get_type()
	{
		return 'affiliation-field';
	}

	public function get_name()
	{
		return esc_html__('Affiliation Field', 'elementor-form-affiliation-field');
	}

	public function render($item, $item_index, $form)
	{

		echo '<div class="full">
        <div id="affList">
            <div class="aff-div">
                <label class="aff-label">1.</label>
                <input type="text" class="aff-width form-padding elementor-field elementor-field-textual" value="" name="affiliation[]" placeholder="(e.g. Vilnius University, Faculty of Physics, Institute of Chemical Physics, Lithuania)">
            </div>
        </div>
        <button type="button" class="aff-add">Add</button>
        <button type="button" class="aff-rem">Remove</button><br>
        </div>';
	}

}