<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Affiliation_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public $depended_scripts = [ 'affiliation-field-js' ];

	public $depended_styles = [ 'registration-widget-style' ];

	public function get_type() {
		return 'affiliation-field';
	}

	public function get_name() {
		return esc_html__( 'Affiliation Field', 'elementor-form-affiliation-field' );
	}

	public function render( $item, $item_index, $form ) {
        
		echo '<div><br>
        <label>Affiliations</label><br>
        <div id="' . $item['custom_id'] . '-List">
            <div>
                <label>1. </label>
                <input type="text" name="aff-' . $item['custom_id'] . '[]" placeholder="Affiliation">
            </div>
        </div><br>
        <button type="button" class="aff-add" data-field-id="' . $item['custom_id'] . '" >Add</button>
        <button type="button" class="aff-rem" data-field-id="' . $item['custom_id'] . '">Remove</button>
        </div>';
	}
	
}