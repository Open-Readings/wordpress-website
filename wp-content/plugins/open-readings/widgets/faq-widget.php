<?php

class Elementor_Faq_Widget extends \Elementor\Widget_Base
{

    public function get_style_depends()
    {
        return ['faq-widget-style'];
    }

    public function get_script_depends()
    {
        return ['faq-widget-js'];
    }

    public function get_name()
    {
        return 'faq_widget';
    }

    public function get_title()
    {
        return esc_html__('FAQ Section', 'elementor-addon');
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
        return ['frequently', 'asked', 'questions'];
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
            'faq_question',
            [
                'label' => esc_html__('Category', 'elementor-faq-control'),
                'type' => 'faq',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {

        global $wpdb;
        $settings = $this->get_settings_for_display();
        $term_id = $settings['faq_question'];
        $post_id = $wpdb->get_results("SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id=$term_id");
        $posts = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_status='publish'");
        foreach ($post_id as $print_id) {
            foreach ($posts as $post) {
                if ($post->ID == $print_id->object_id) {
?>
                    <div class="collapsible"><div class="faq-plus">+</div><div class="faq-question"><?php echo $post->post_title ?></div></div>
                    <div class="content">
                        <?php echo wpautop($post->post_content) ?>
                    </div>
<?php
                    break;
                }
            }
        }
    }
}
