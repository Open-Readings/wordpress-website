<?php

class Elementor_Registration_Control extends \Elementor\Base_Data_Control
{

	public function get_type()
	{
		return 'registration';
	}

	public static function get_questions()
	{
        return NULL;
	}

	protected function get_default_settings()
	{
		return [
			'registration' => self::get_questions(),
		];
	}

	// public function get_default_value()
	// {
	// 	return 'faq_practical';
	// }

	public function content_template()
	{

	}
}
