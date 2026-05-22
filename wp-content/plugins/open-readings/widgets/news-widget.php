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
        <div class="scroll-wrapper">
            <a class="left-button"><i class="or-arrow or-left"></i></a>
            <div class="image-scroll-container">
                <div class="image-scroll-content news-container">
                    <?php
                    global $wpdb;
                    $results = $wpdb->get_results('SELECT post_date, post_title, ID FROM wp_posts WHERE post_type="news" AND post_status="publish" ORDER BY post_date DESC');
                    
                    $index = 0;
                    foreach ($results as $row) {
                        $result_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id=%d AND meta_key = 'news_thumbnail'", $row->ID));
                        $result_url = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id=%d AND meta_key = 'news_link'", $row->ID));
                        
                        $date = (new DateTime($row->post_date))->format('Y-m-d');
                        
                        // Set attributes dynamically for performance
                        $img_attributes = array(
                            'class' => 'news-img',
                            'alt'   => $row->post_title,
                        );

                        // First image gets high priority for LCP; others get lazy loaded
                        if ($index === 0) {
                            $img_attributes['fetchpriority'] = 'high';
                            $img_attributes['loading'] = 'eager'; // Don't lazy load the very first visible item
                        } else {
                            $img_attributes['loading'] = 'lazy';
                        }
                        
                        ?>
                        <a href="<?php echo esc_url($result_url); ?>" class="news-post">
                            <div class="news-image-background">
                                <?php 
                                // This outputs a dynamic <img> tag with srcset automatically!
                                if ($result_id) {
                                    echo wp_get_attachment_image($result_id, 'medium_large', false, $img_attributes); 
                                }
                                ?>
                            </div>
                            <p class="news-date"><?php echo esc_html($date); ?></p>
                            <p class="news-title"><?php echo esc_html($row->post_title); ?></p>
                            <p class="news-link">Read more >></p>
                        </a>
                        <?php
                        $index++;
                    }
                    ?>
                </div>
                </div>
                    <a class="right-button or-right"><i class="or-arrow"></i></a>
                </div>
        <?php
    }
    
    //<!-- display flex -->
    // <img src="https://openreadings.eu/wp-content/uploads/2024/05/OR-visi-300x200.jpg">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/regisa-e1736591700576-300x155.jpg">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/Renata-Minkeviciute_Cafe-Scientifique_Facebook-300x157.png">
    // <img src="https://openreadings.eu/wp-content/uploads/2025/01/OR-300x192.jpg">


}
