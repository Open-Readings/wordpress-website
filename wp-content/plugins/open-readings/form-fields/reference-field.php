<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Reference_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public $depended_scripts = [ 'reference-field-js' ];

	public $depended_styles = [ 'registration-widget-style' ];

	public function get_type() {
		return 'reference-field';
	}

	public function get_name() {
		return esc_html__( 'Reference Field', 'elementor-form-reference-field' );
	}

	public function render( $item, $item_index, $form ) {
		echo '
        <div class="full">
        <div id="refList">
			<div class="ref-div">
				<label class="ref-label">1. </label><input type="text" class="ref-width form-padding elementor-field elementor-field-textual" name="references[]" value="Example M. A. Green, High Efficiency Silicon Solar Cells (Trans. Tech. Publications, Switzerland, 1987)" placeholder="(e.g. M.A.Green, HighEfficiencySiliconSolarCells (Trans. Tech. Publications, Switzerland, 1987).)">
			</div>
        </div>
        <button type="button" class="ref-add">Add</button>
        <button type="button" class="ref-rem">Remove</button><br>
        </div>';
	}
}