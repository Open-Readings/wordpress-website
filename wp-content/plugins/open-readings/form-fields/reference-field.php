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
        <div>
        <label>References</label> <br>
        <div id="' . $item['custom_id'] . '-List">
        </div><br>
        <button type="button" class="ref-add" data-field-id="' . $item['custom_id'] . '">Add</button>
        <button type="button" class="ref-rem" data-field-id="' . $item['custom_id'] . '">Remove</button>
        </div>';
	}
	
}