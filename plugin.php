<?php

/**
 * Plugin Name:     Custom Wheel
 * Description:     The plugin adds an interactive spinning wheel with prize management.
 * Version:         2.0.0
 * Author:          CEA Informatics
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wp-plugin-wheel
 *
 * @package         wp-plugin-wheel
 */

if (!defined('ABSPATH')) exit;

define('WPW_VERSION', '2.0.0');
define('WPW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPW_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WPW_PLUGIN_DIR . 'includes/class-wpw-db.php';
require_once WPW_PLUGIN_DIR . 'includes/class-wpw-admin.php';
require_once WPW_PLUGIN_DIR . 'includes/class-wpw-ajax.php';

register_activation_hook(__FILE__, array('WPW_DB', 'install'));

WPW_Admin::init();
WPW_Ajax::init();

function wpw_display_wheel() {
    $prizes = WPW_DB::get_prizes(true);

    if (empty($prizes)) {
        return '<p>' . esc_html__('The wheel is not configured yet.', 'wp-plugin-wheel') . '</p>';
    }

    $is_logged_in = is_user_logged_in();
    $has_played = $is_logged_in && WPW_DB::has_participated_today(get_current_user_id());

    $prizes_data = array();
    foreach ($prizes as $prize) {
        $prizes_data[] = array(
            'id'   => $prize->id,
            'name' => $prize->name,
        );
    }

    $disabled = (!$is_logged_in || $has_played) ? ' disabled' : '';

    ob_start(); ?>
    <div id="wpw-container">
        <div id="wpw-wheel-wrapper">
            <svg id="wpw-pointer" xmlns="http://www.w3.org/2000/svg" width="42" height="50" viewBox="0 0 28 32" overflow="visible">
            <path d="M6,0 Q0,0 0,6 L14,32 L28,6 Q28,0 22,0 Z" fill="#323232" stroke="white" stroke-width="3" stroke-linejoin="round" paint-order="stroke fill"/>
        </svg>
            <div id="wp-wheel" data-prizes="<?php echo esc_attr(wp_json_encode($prizes_data)); ?>">
                <canvas id="wpw-canvas" width="400" height="400"></canvas>
            </div>
            <div id="wpw-center-cap"></div>
        </div>
        <?php if (!$is_logged_in): ?>
            <p class="wpw-message"><?php esc_html_e('Please log in to participate.', 'wp-plugin-wheel'); ?></p>
        <?php elseif ($has_played): ?>
            <p class="wpw-message"><?php esc_html_e('You already played today. Come back tomorrow!', 'wp-plugin-wheel'); ?></p>
        <?php endif; ?>
        <button id="wp-wheel-spin"<?php echo $disabled; ?>><?php esc_html_e('Spin the wheel', 'wp-plugin-wheel'); ?></button>
        <div id="wpw-result"></div>
    </div>
    <?php
    return ob_get_clean();
}

function wpw_enqueue_scripts() {
    wp_enqueue_style('wpw-style', WPW_PLUGIN_URL . 'assets/wheel.css', array(), WPW_VERSION);
    wp_enqueue_script('wpw-script', WPW_PLUGIN_URL . 'assets/wheel.js', array(), WPW_VERSION, true);
    wp_localize_script('wpw-script', 'wpw_ajax', array(
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpw_spin_nonce'),
    ));
}

add_action('wp_enqueue_scripts', 'wpw_enqueue_scripts');

add_shortcode('wp-wheel', 'wpw_display_wheel');
