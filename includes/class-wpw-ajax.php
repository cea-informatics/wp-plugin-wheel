<?php

if (!defined('ABSPATH')) exit;

class WPW_Ajax {

    public static function init() {
        add_action('wp_ajax_wpw_spin', array(__CLASS__, 'handle_spin'));
        add_action('wp_ajax_nopriv_wpw_spin', array(__CLASS__, 'handle_spin_nopriv'));
    }

    public static function handle_spin() {
        check_ajax_referer('wpw_spin_nonce', 'nonce');

        $user_id        = get_current_user_id();
        $daily_attempts = (int) get_option('wpw_daily_attempts', 2);
        $daily_limit    = (int) get_option('wpw_daily_limit', 10);

        $attempts_today = WPW_DB::count_today_attempts($user_id);

        if ($attempts_today >= $daily_attempts) {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('Vous avez utilisé vos %d tentatives pour aujourd\'hui. Revenez demain !', 'wp-plugin-wheel'),
                    $daily_attempts
                ),
            ));
        }

        $prizes = WPW_DB::get_prizes(true);
        $total  = count($prizes);

        if ($total === 0) {
            wp_send_json_error(array(
                'message' => __('La roue n\'est pas disponible pour le moment.', 'wp-plugin-wheel'),
            ));
        }

        $wins_today  = WPW_DB::count_today_wins();
        $force_loss  = ($wins_today >= $daily_limit);
        $prize       = $force_loss ? null : WPW_DB::pick_random_prize();
        $won         = ($prize !== null);

        if ($won) {
            $index = 0;
            foreach ($prizes as $i => $p) {
                if ($p->id === $prize->id) {
                    $index = $i;
                    break;
                }
            }
        } else {
            $index = mt_rand(0, $total - 1);
        }

        WPW_DB::record_participation($user_id, $won ? $prize->id : null, $won);

        $remaining = max(0, $daily_attempts - $attempts_today - 1);

        wp_send_json_success(array(
            'won'               => $won,
            'prize'             => $won ? array(
                'id'          => $prize->id,
                'name'        => $prize->name,
                'description' => $prize->description,
            ) : null,
            'index'             => $index,
            'total'             => $total,
            'remaining_attempts' => $remaining,
        ));
    }

    public static function handle_spin_nopriv() {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté pour participer.', 'wp-plugin-wheel'),
        ));
    }
}
