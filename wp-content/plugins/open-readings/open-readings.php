<?php

/**
 * Open Readings
 *
 * @package           PluginPackage
 * @author            Open Readings
 * @copyright         2023 Open Readings
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Open Readings
 * Description:       Plugin for Open Readings website
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Open Readings
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://example.com/my-plugin/
 */

function register_faq_widget($widgets_manager)
{
  require_once(__DIR__ . '/widgets/faq-widget.php');
  $widgets_manager->register(new \Elementor_Faq_Widget());
}

add_action('elementor/widgets/register', 'register_faq_widget');

function register_or_dependencies()
{
  wp_register_style('faq-widget-style', plugins_url('assets/css/faq-widget-style.css', __FILE__));
  wp_register_script('faq-widget-js', plugins_url('assets/js/faq-widget-js.js', __FILE__));
}

add_action('wp_enqueue_scripts', 'register_or_dependencies');

function register_faq_controls($controls_manager)
{
  require_once(__DIR__ . '/controls/faq-controls.php');
  $controls_manager->register(new \Elementor_FAQ_Control());
}

add_action('elementor/controls/register', 'register_faq_controls');
