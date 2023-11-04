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
        <div class="full">
			<div id="authList">
				<div>
					<input type="text" pattern="^[^\\\^_]+$" name="name[]" placeholder="Full Name" required>
					<input type="text" pattern="[0-9, ]*" class="narrow" name="aff_ref[]" placeholder="Aff. Nr." required>
					<input type="email" name="email-author" placeholder="Email">
				</div>
			</div>
			<button type="button" class="auth-add" >Add</button>
			<button type="button" class="auth-rem">Remove</button><br>
		</div>';
	}
}