<?php

if (!defined('ABSPATH')) exit;

class WPW_Ajax {

    public static function init() {
        add_action('wp_ajax_wpw_spin', array(__CLASS__, 'handle_spin'));
        add_action('wp_ajax_nopriv_wpw_spin', array(__CLASS__, 'handle_spin_nopriv'));
    }

    /**
     * Handle spin for logged-in users.
     */
    public static function handle_spin() {
        check_ajax_referer('wpw_spin_nonce', 'nonce');

        $user_id = get_current_user_id();

        if (WPW_DB::has_participated_today($user_id)) {
            wp_send_json_error(array(
                'message' => __('You already played today. Come back tomorrow!', 'wp-plugin-wheel'),
            ));
        }

        $prize = WPW_DB::pick_random_prize();

        if (!$prize) {
            wp_send_json_error(array(
                'message' => __('No prizes available at the moment.', 'wp-plugin-wheel'),
            ));
        }

        $prizes = WPW_DB::get_prizes(true);
        $total = count($prizes);
        $index = 0;

        foreach ($prizes as $i => $p) {
            if ($p->id === $prize->id) {
                $index = $i;
                break;
            }
        }

        $result = WPW_DB::record_participation($user_id, $prize->id);

        if ($result === false) {
            wp_send_json_error(array(
                'message' => __('You already played today. Come back tomorrow!', 'wp-plugin-wheel'),
            ));
        }

        wp_send_json_success(array(
            'prize' => array(
                'id'          => $prize->id,
                'name'        => $prize->name,
                'description' => $prize->description,
            ),
            'index' => $index,
            'total' => $total,
        ));
    }

    /**
     * Handle spin for non-logged-in users.
     */
    public static function handle_spin_nopriv() {
        wp_send_json_error(array(
            'message' => __('You must be logged in to participate.', 'wp-plugin-wheel'),
        ));
    }
}
