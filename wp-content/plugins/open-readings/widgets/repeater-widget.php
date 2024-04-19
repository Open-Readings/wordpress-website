<?php

class Elementor_Repeater_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return "repeater_widget";
    }

    public function get_title()
    {
        return esc_html__('Repeater Widget', 'OR');
    }

    public function get_icon()
    {
        return 'eicon-post-list';
    }

    public function get_style_depends()
    {
        return ['repeater-widget-style'];
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_keywords()
    {
        return ['repeater', 'widget'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'elementor-faq-control'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label' => esc_html__('Post Type', 'elementor-faq-control'),
                'type' => 'faq',
            ]
        );

        $this->end_controls_section();
    }

}

?>