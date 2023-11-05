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
					<input type="email" name="email-author" placeholder="Contact email" required><br>

					<input type="text" pattern="^[^&%\$\\#^_\{\}~]*$" name="name[]" placeholder="Full name" required>
					<input type="text" pattern="[0-9, ]*" class="narrow" name="aff_ref[]" placeholder="Aff. Nr." required>
					<label class="text-like-elementor"> Contact Author </label> <input type="radio" name="contact_author" value="1">
				</div>
			</div>
			<button type="button" class="auth-add" >Add</button>
			<button type="button" class="auth-rem">Remove</button><br>
		</div>';
	}
}