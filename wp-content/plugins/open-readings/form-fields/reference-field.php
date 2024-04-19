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
        </div>
        <button type="button" class="ref-add">Add</button>
        <button type="button" class="ref-rem">Remove</button><br>
        </div>';
	}
}