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
    ?>
    <div class="or-news-carousel-wrapper">
        <div class="or-news-nav-btn btn-left">
            <i class="or-arrow or-left"></i>
        </div>
        
        <div class="or-news-scroll-container">
            <div class="or-news-scroll-content">
                <?php
                global $wpdb;
                $results = $wpdb->get_results('SELECT post_date, post_title, ID FROM wp_posts WHERE post_type="news" AND post_status="publish" ORDER BY post_date DESC');
                
                $index = 0;
                foreach ($results as $row) {
                    $result_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id=%d AND meta_key = 'news_thumbnail'", $row->ID));
                    $result_url = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id=%d AND meta_key = 'news_link'", $row->ID));
                    
                    $date = (new DateTime($row->post_date))->format('Y-m-d');
                    
                    $img_attributes = array(
                        'class' => 'news-img',
                        'alt'   => $row->post_title,
                    );

                    // Native priority optimization
                    if ($index === 0) {
                        $img_attributes['fetchpriority'] = 'high';
                        $img_attributes['loading'] = 'eager';
                    } else {
                        $img_attributes['loading'] = 'lazy';
                    }
                    ?>
                    
                    <a href="<?php echo esc_url($result_url); ?>" class="news-post">
                        <div class="news-image-background">
                            <?php 
                            if ($result_id) {
                                // 1. Define how wide this image will be in your CSS layout
                                // On mobile (<768px) it's 100vw, on desktop it's fixed around 360px
                                $sizes = '(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 360px';
                                
                                // 2. Add 'sizes' to your attributes array
                                $img_attributes['sizes'] = $sizes;

                                // 3. Use 'medium_large' OR 'large' here. 
                                // IMPORTANT: Because we added 'sizes', the browser will IGNORE the name 
                                // and use the browser-native logic to pick the smallest/best file.
                                echo wp_get_attachment_image($result_id, 'large', false, $img_attributes); 
                            }
                            ?>
                        </div>
                        <p class="news-date"><?php echo esc_html($date); ?></p>
                        <h3 class="news-title"><?php echo esc_html($row->post_title); ?></h3>
                        <span class="news-link-action">Read more &gt;&gt;</span>
                    </a>
                    
                    <?php
                    $index++;
                }
                ?>
            </div>
        </div>

        <div class="or-news-nav-btn btn-right">
            <i class="or-arrow or-right"></i>
        </div>
    </div>
    <?php
}
    
    //<!-- display flex -->
    // <img src="https://openreadings.eu/wp-content/uploads/2024/05/OR-visi-300x200.jpg">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/regisa-e1736591700576-300x155.jpg">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/Renata-Minkeviciute_Cafe-Scientifique_Facebook-300x157.png">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/OR-300x192.jpg">


}
