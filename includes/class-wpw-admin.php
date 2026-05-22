<?php

if (!defined('ABSPATH')) exit;

class WPW_Admin {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menus'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function register_menus() {
        add_menu_page(
            __('Custom Wheel', 'wp-plugin-wheel'),
            __('Custom Wheel', 'wp-plugin-wheel'),
            'manage_options',
            'wpw-prizes',
            array(__CLASS__, 'page_prizes'),
            'dashicons-marker',
            30
        );

        add_submenu_page(
            'wpw-prizes',
            __('Prizes', 'wp-plugin-wheel'),
            __('Prizes', 'wp-plugin-wheel'),
            'manage_options',
            'wpw-prizes',
            array(__CLASS__, 'page_prizes')
        );

        add_submenu_page(
            'wpw-prizes',
            __('Participations', 'wp-plugin-wheel'),
            __('Participations', 'wp-plugin-wheel'),
            'manage_options',
            'wpw-participations',
            array(__CLASS__, 'page_participations')
        );

        add_submenu_page(
            'wpw-prizes',
            __('Settings', 'wp-plugin-wheel'),
            __('Settings', 'wp-plugin-wheel'),
            'manage_options',
            'wpw-settings',
            array(__CLASS__, 'page_settings')
        );
    }

    public static function enqueue_assets($hook) {
        if (strpos($hook, 'wpw-') === false) {
            return;
        }

        wp_enqueue_style('wpw-admin', WPW_PLUGIN_URL . 'assets/admin.css', array(), WPW_VERSION);
    }

    /* -------------------------------------------------------------------------
     * Prizes page
     * ---------------------------------------------------------------------- */

    public static function page_prizes() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'wp-plugin-wheel'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handle_prize_save();
            return;
        }

        if ($action === 'delete' && isset($_GET['id'])) {
            self::handle_prize_delete();
            return;
        }

        if ($action === 'new' || $action === 'edit') {
            $prize = null;
            if ($action === 'edit' && isset($_GET['id'])) {
                $prize = WPW_DB::get_prize(absint($_GET['id']));
            }
            include WPW_PLUGIN_DIR . 'admin/views/prizes-form.php';
        } else {
            $prizes = WPW_DB::get_prizes();
            include WPW_PLUGIN_DIR . 'admin/views/prizes-list.php';
        }
    }

    private static function handle_prize_save() {
        if (!check_admin_referer('wpw_save_prize', 'wpw_nonce')) {
            wp_die(__('Invalid nonce.', 'wp-plugin-wheel'));
        }

        $data = array(
            'name'        => isset($_POST['name']) ? $_POST['name'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'probability' => isset($_POST['probability']) ? $_POST['probability'] : 1,
            'stock'       => isset($_POST['stock']) ? $_POST['stock'] : '',
            'image_url'   => isset($_POST['image_url']) ? $_POST['image_url'] : '',
            'active'      => isset($_POST['active']) ? 1 : 0,
        );

        if (!empty($_POST['prize_id'])) {
            WPW_DB::update_prize(absint($_POST['prize_id']), $data);
            $message = 'updated';
        } else {
            WPW_DB::insert_prize($data);
            $message = 'created';
        }

        wp_redirect(admin_url('admin.php?page=wpw-prizes&message=' . $message));
        exit;
    }

    private static function handle_prize_delete() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpw_delete_prize_' . absint($_GET['id']))) {
            wp_die(__('Invalid nonce.', 'wp-plugin-wheel'));
        }

        WPW_DB::delete_prize(absint($_GET['id']));

        wp_redirect(admin_url('admin.php?page=wpw-prizes&message=deleted'));
        exit;
    }

    /* -------------------------------------------------------------------------
     * Participations page
     * ---------------------------------------------------------------------- */

    public static function page_participations() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'wp-plugin-wheel'));
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = absint($_GET['id']);
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpw_delete_participation_' . $id)) {
                wp_die(__('Invalid nonce.', 'wp-plugin-wheel'));
            }
            WPW_DB::delete_participation($id);
            wp_redirect(admin_url('admin.php?page=wpw-participations&message=deleted'));
            exit;
        }

        $per_page    = 20;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $offset      = ($current_page - 1) * $per_page;

        $participations = WPW_DB::get_participations($per_page, $offset);
        $total          = WPW_DB::count_participations();
        $total_pages    = ceil($total / $per_page);
        $deleted        = isset($_GET['message']) && $_GET['message'] === 'deleted';

        include WPW_PLUGIN_DIR . 'admin/views/participations-list.php';
    }

    /* -------------------------------------------------------------------------
     * Settings page
     * ---------------------------------------------------------------------- */

    public static function page_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'wp-plugin-wheel'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!check_admin_referer('wpw_save_settings', 'wpw_nonce')) {
                wp_die(__('Invalid nonce.', 'wp-plugin-wheel'));
            }

            update_option('wpw_daily_limit',       max(1, absint($_POST['daily_limit'])));
            update_option('wpw_daily_attempts',    max(1, absint($_POST['daily_attempts'])));
            update_option('wpw_lose_probability',  absint($_POST['lose_probability']));

            wp_redirect(admin_url('admin.php?page=wpw-settings&message=saved'));
            exit;
        }

        $daily_limit      = (int) get_option('wpw_daily_limit', 10);
        $daily_attempts   = (int) get_option('wpw_daily_attempts', 2);
        $lose_probability = (int) get_option('wpw_lose_probability', 0);

        include WPW_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
