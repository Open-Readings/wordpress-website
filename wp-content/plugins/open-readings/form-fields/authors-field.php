<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Authors_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public $depended_scripts = [ 'authors-field-js' ];

	public $depended_styles = [ 'registration-widget-style' ];

	public function get_type() {
		return 'authors-field';
	}

	public function get_name() {
		return esc_html__( 'Authors Field', 'elementor-form-authors-field' );
	}

	public function render( $item, $item_index, $form ) {
		echo '
        <div>
        <label>Authors</label><br>
        <div id="' . $item["custom_id"] . '-List">
            <div>
                <input type="text" name="name-' . $item["custom_id"] . '[]" placeholder="Name">
                <input type="number" name="reference-' . $item["custom_id"] . '[]" placeholder="Affiliation">
                <input type="email" name="email-' . $item["custom_id"] . '" placeholder="Email">
            </div>
        </div>
        <button type="button" class="auth-add" data-field-id="' . $item['custom_id'] . '">Add</button>
        <button type="button" class="auth-rem" data-field-id="' . $item['custom_id'] . '">Remove</button></div>';
	}
}