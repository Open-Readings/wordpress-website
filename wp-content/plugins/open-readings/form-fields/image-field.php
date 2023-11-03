<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Elementor_Image_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public $depended_scripts = [ 'image-field-js' ];

	public $depended_styles = [ 'registration-widget-style' ];

	public function get_type() {
		return 'image-field';
	}

	public function get_name() {
		return esc_html__( 'Image Field', 'elementor-form-image-field' );
	}

	public function render( $item, $item_index, $form ) {
        echo '<div class="full">
        <input type="file" id="fileInput" multiple>
		<div class="flex-div">
		<button id="fileButton" data-field-id="' . $item['custom_id'] . '">Upload File</button>
        <div class="loader" id="uploadLoader"></div>
        </div>
        
        </div>';
    }
}