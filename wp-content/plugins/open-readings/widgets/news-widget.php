<?php

class Elementor_News_Widget extends \Elementor\Widget_Base
{

    public function get_style_depends()
    {
        return ['news-widget-style'];
    }

    public function get_script_depends()
    {
        return ['news-section'];
    }

    public function get_name()
    {
        return 'news_widget';
    }

    public function get_title()
    {
        return esc_html__('news Section', 'elementor-addon');
    }

    public function get_icon()
    {
        return 'eicon-help-o';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_keywords()
    {
        return ['news', 'section'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'elementor-news-control'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        echo '
        <div class="scroll-wrapper">
            <button class="scroll-left">⬅</button>
            <div class="image-scroll-container">
                <img src="' . get_template_directory_uri() . '/assets/image1.jpg" alt="Image 1">
                <img src="' . get_template_directory_uri() . '/assets/image2.jpg" alt="Image 2">
                <img src="' . get_template_directory_uri() . '/assets/image3.jpg" alt="Image 3">
                <img src="' . get_template_directory_uri() . '/assets/image4.jpg" alt="Image 4">
                <img src="' . get_template_directory_uri() . '/assets/image5.jpg" alt="Image 5">
                <img src="' . get_template_directory_uri() . '/assets/image6.jpg" alt="Image 6">
                <img src="' . get_template_directory_uri() . '/assets/image7.jpg" alt="Image 7">
                <img src="' . get_template_directory_uri() . '/assets/image8.jpg" alt="Image 8">
                <img src="' . get_template_directory_uri() . '/assets/image9.jpg" alt="Image 9">
                <img src="' . get_template_directory_uri() . '/assets/image10.jpg" alt="Image 10">
                <img src="' . get_template_directory_uri() . '/assets/image11.jpg" alt="Image 11">
                <img src="' . get_template_directory_uri() . '/assets/image12.jpg" alt="Image 12">
            </div>
            <button class="scroll-right">➡</button>
        </div>';
    }
    
}
