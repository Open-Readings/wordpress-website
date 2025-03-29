<?php

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

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
        // $desiredIndex = 3; 
    
        ?>
        <div class="scroll-wrapper">
            <button class="scroll-left">⬅</button>
            <div class="image-scroll-container">
                <div class="image-scroll-content news-container">
                    <?php
                    global $wpdb;
                    $result = $wpdb->get_results('SELECT post_title, ID FROM wp_posts WHERE post_type="news"');
                    
                    foreach ($result as $row) {
                        $result_id = $wpdb->get_var("SELECT meta_value FROM wp_postmeta WHERE post_id=$row->ID and meta_key = 'news_thumbnail'");
                        $result_url = $wpdb->get_var("SELECT meta_value FROM wp_postmeta WHERE post_id=$row->ID and meta_key = 'news_link'");
                        $result_img = $wpdb->get_var("SELECT `guid` FROM wp_posts WHERE ID=$result_id");
                        ?>
                            <a href="<?php echo $result_url ?>" class="news-post">
                                <img class="news-img" src="<?php echo $result_img ?>">
                                <p class="news-title"><?php echo $row->post_title; ?></p>
                            </a>
                            <?php
                    }

                    ?>
                </div>
            </div>
            <button class="scroll-right">➡</button>
        </div>
        <?php
    }    
    
    //<!-- display flex -->
    // <img src="https://openreadings.eu/wp-content/uploads/2024/05/OR-visi-300x200.jpg">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/regisa-e1736591700576-300x155.jpg">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/Renata-Minkeviciute_Cafe-Scientifique_Facebook-300x157.png">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/OR-300x192.jpg">


}
