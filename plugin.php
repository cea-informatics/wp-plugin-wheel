<?php

/**
 * Plugin Name:     Custom Wheel
 * Description:     The plugin adds an interactive spinning wheel with prize management.
 * Version:         2.5.0
 * Author:          CEA Informatics
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wp-plugin-wheel
 *
 * @package         wp-plugin-wheel
 */

if (!defined('ABSPATH')) exit;

define('WPW_VERSION', '2.4.0');
define('WPW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPW_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WPW_PLUGIN_DIR . 'includes/class-wpw-db.php';
require_once WPW_PLUGIN_DIR . 'includes/class-wpw-admin.php';
require_once WPW_PLUGIN_DIR . 'includes/class-wpw-ajax.php';

register_activation_hook(__FILE__, array('WPW_DB', 'install'));

add_action('plugins_loaded', array('WPW_DB', 'maybe_migrate'));

WPW_Admin::init();
WPW_Ajax::init();

function wpw_display_wheel() {
    $prizes = WPW_DB::get_prizes(true);

    if (empty($prizes)) {
        return '<p>' . esc_html__('La roue n\'est pas encore configurée.', 'wp-plugin-wheel') . '</p>';
    }

    $is_logged_in   = is_user_logged_in();
    $max_attempts   = (int) get_option('wpw_daily_attempts', 2);
    $attempts_today = $is_logged_in ? WPW_DB::count_today_attempts(get_current_user_id()) : 0;
    $remaining      = max(0, $max_attempts - $attempts_today);
    $has_played_max = ($attempts_today >= $max_attempts);

    $prizes_data = array();
    foreach ($prizes as $prize) {
        $prizes_data[] = array(
            'id'          => $prize->id,
            'name'        => $prize->name,
            'description' => $prize->description,
        );
    }

    $disabled = (!$is_logged_in || $has_played_max) ? ' disabled' : '';

    ob_start(); ?>
    <div id="wpw-modal" class="wpw-modal" aria-hidden="true">
        <div class="wpw-modal-backdrop"></div>
        <div class="wpw-modal-box" role="dialog" aria-modal="true">
            <div class="wpw-modal-icon" id="wpw-modal-icon">🎉</div>
            <h3 class="wpw-modal-title" id="wpw-modal-title">Félicitations&nbsp;!</h3>
            <p class="wpw-modal-subtitle" id="wpw-modal-subtitle">Vous avez gagné</p>
            <p class="wpw-modal-prize" id="wpw-modal-prize"></p>
            <p class="wpw-modal-prize-desc" id="wpw-modal-prize-desc"></p>
            <button class="wpw-modal-close" id="wpw-modal-close">Super, merci&nbsp;!</button>
        </div>
    </div>
    <div id="wpw-container">
        <div id="wpw-content">
            <h2 class="wpw-title">Tournez la roue &amp; Gagnez votre surprise&nbsp;!</h2>
            <p class="wpw-description">Faites tourner notre roulette et débloquez une offre réservée à nos visiteurs.</p>
            <p class="wpw-description">Remises privées, cadeaux surprises ou avantages spéciaux… laissez le hasard vous récompenser.</p>
            <div class="wpw-actions">
                <?php if ($has_played_max && $is_logged_in): ?>
                    <p class="wpw-message"><?php printf(
                        esc_html__('Vous avez utilisé vos %d tentatives pour aujourd\'hui. Revenez demain !', 'wp-plugin-wheel'),
                        $max_attempts
                    ); ?></p>
                <?php elseif ($is_logged_in && $remaining < $max_attempts): ?>
                    <p class="wpw-attempts" id="wpw-attempts-left"><?php printf(
                        esc_html(_n('Il vous reste %d tentative aujourd\'hui.', 'Il vous reste %d tentatives aujourd\'hui.', $remaining, 'wp-plugin-wheel')),
                        $remaining
                    ); ?></p>
                <?php endif; ?>
                <div class="wpw-buttons">
                    <button id="wp-wheel-spin"<?php echo $disabled; ?>>Tourner la roue</button>
                    <?php if (!$is_logged_in): ?>
                        <a href="/login" class="wpw-login-btn">Se connecter</a>
                    <?php endif; ?>
                </div>
                <div id="wpw-result"></div>
            </div>
        </div>
        <div id="wpw-wheel-wrapper">
            <svg id="wpw-pointer" xmlns="http://www.w3.org/2000/svg" width="42" height="50" viewBox="0 0 28 32" overflow="visible">
                <path d="M6,0 Q0,0 0,6 L14,32 L28,6 Q28,0 22,0 Z" fill="#323232" stroke="white" stroke-width="3" stroke-linejoin="round" paint-order="stroke fill"/>
            </svg>
            <div id="wp-wheel" data-prizes="<?php echo esc_attr(wp_json_encode($prizes_data)); ?>">
                <canvas id="wpw-canvas" width="400" height="400"></canvas>
            </div>
            <div id="wpw-center-cap"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function wpw_enqueue_scripts() {
    wp_enqueue_style('wpw-style', WPW_PLUGIN_URL . 'assets/wheel.css', array(), WPW_VERSION);
    wp_enqueue_script('canvas-confetti', 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js', array(), null, true);
    wp_enqueue_script('wpw-script', WPW_PLUGIN_URL . 'assets/wheel.js', array('canvas-confetti'), WPW_VERSION, true);
    wp_localize_script('wpw-script', 'wpw_ajax', array(
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpw_spin_nonce'),
    ));
}

add_action('wp_enqueue_scripts', 'wpw_enqueue_scripts');

add_shortcode('wp-wheel', 'wpw_display_wheel');
