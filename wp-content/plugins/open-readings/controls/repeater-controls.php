<?php

class Elementor_Repeater_Control extends \Elementor\Base_Data_Control
{

    public function get_type()
    {
        return 'repeater';
    }

   

    protected function get_default_settings()
    {
        return [
            'post type' => get_post_types()
        ];
    }

    public function get_default_value()
    {
        return 'invited_speaker';
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
                            <option value="">
                                <?php echo esc_html__('Select Category'); ?>
                            </option>
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