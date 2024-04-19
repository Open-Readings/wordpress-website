<?php

class Elementor_FAQ_Control extends \Elementor\Base_Data_Control
{

	public function get_type()
	{
		return 'faq';
	}

	public static function get_questions()
	{
		global $wpdb;
		$term_results = $wpdb->get_results("SELECT * FROM wp_terms");
		$term_ids = $wpdb->get_col("SELECT term_id FROM wp_term_taxonomy WHERE taxonomy='category'");
		$category_array = array();
		foreach ($term_results as $term_result) {
			if (in_array($term_result->term_id, $term_ids)) {
				$category_array[$term_result->term_id] = $term_result->name;
			}
		}
		return $category_array;
	}

	protected function get_default_settings()
	{
		return [
			'faq' => self::get_questions(),
		];
	}

	public function get_default_value()
	{
		return 'faq_practical';
	}

	public function content_template()
	{
		$control_uid = $this->get_control_uid();
?>
		<div class="elementor-control-field">

			<# if ( data.label ) {#>
			<label for="<?php echo $control_uid; ?>" class="elementor-control-title"> {{{ data.label }}}</label>
			<# } #>
			
			<div class="elementor-control-input-wrapper">
				<select id="<?php echo $control_uid; ?>" data-setting="{{ data.name }}">
					<option value=""><?php echo esc_html__('Select Category'); ?></option>
					<# _.each( data.faq, function( faq_label, faq_value ) { #>
					<option value="{{ faq_value }}">{{{ faq_label }}}</option>
					<# } ); #>
				</select>
			</div>

		</div>
		
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
