<?php

/**
 * Plugin Name:     wp-plugin-wheel
 * Description:     WordPress plugin to display a spinning wheel.
 * Version:         1.0.2
 * Author:          CEA Informatics
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wp-plugin-wheel
 *
 * @package         wp-plugin-wheel
 */

if (!defined('ABSPATH')) exit;

function wpw_display_wheel() {
    ob_start(); ?>
    <div id="wp-wheel"></div>
    <button id="wp-wheel-spin">Tourner la roue</button>
    <?php
    return ob_get_clean();
}

function wpw_enqueue_scripts() {
    wp_enqueue_style('wpw-style', plugin_dir_url(__FILE__) . 'assets/wheel.css');
    wp_enqueue_script('wpw-script', plugin_dir_url(__FILE__) . 'assets/wheel.js', array(), false, true);
}

add_action('wp_enqueue_scripts', 'wpw_enqueue_scripts');

add_shortcode('wp-wheel', 'wpw_display_wheel');

