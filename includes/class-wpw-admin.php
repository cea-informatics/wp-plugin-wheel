<?php

if (!defined('ABSPATH')) exit;

class WPW_Admin {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menus'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    /**
     * Register admin menus.
     */
    public static function register_menus() {
        add_menu_page(
            __('Roue Personnalisée', 'wp-plugin-wheel'),
            __('Roue Personnalisée', 'wp-plugin-wheel'),
            'manage_options',
            'wpw-prizes',
            array(__CLASS__, 'page_prizes'),
            'dashicons-marker',
            30
        );

        add_submenu_page(
            'wpw-prizes',
            __('Lots', 'wp-plugin-wheel'),
            __('Lots', 'wp-plugin-wheel'),
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
    }

    /**
     * Enqueue admin assets.
     */
    public static function enqueue_assets($hook) {
        if (strpos($hook, 'wpw-') === false) {
            return;
        }

        wp_enqueue_style('wpw-admin', WPW_PLUGIN_URL . 'assets/admin.css', array(), WPW_VERSION);
    }

    /**
     * Prizes page router.
     */
    public static function page_prizes() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Accès non autorisé.', 'wp-plugin-wheel'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

        // Handle form submissions.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handle_prize_save();
            return;
        }

        // Handle delete action.
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

    /**
     * Handle prize save (insert/update).
     */
    private static function handle_prize_save() {
        if (!check_admin_referer('wpw_save_prize', 'wpw_nonce')) {
            wp_die(__('Nonce invalide.', 'wp-plugin-wheel'));
        }

        $data = array(
            'name'        => isset($_POST['name']) ? $_POST['name'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'probability' => isset($_POST['probability']) ? $_POST['probability'] : 1,
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

    /**
     * Handle prize deletion.
     */
    private static function handle_prize_delete() {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpw_delete_prize_' . absint($_GET['id']))) {
            wp_die(__('Nonce invalide.', 'wp-plugin-wheel'));
        }

        WPW_DB::delete_prize(absint($_GET['id']));

        wp_redirect(admin_url('admin.php?page=wpw-prizes&message=deleted'));
        exit;
    }

    /**
     * Participations page.
     */
    public static function page_participations() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Accès non autorisé.', 'wp-plugin-wheel'));
        }

        // Handle delete action.
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = absint($_GET['id']);
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpw_delete_participation_' . $id)) {
                wp_die(__('Nonce invalide.', 'wp-plugin-wheel'));
            }
            WPW_DB::delete_participation($id);
            wp_redirect(admin_url('admin.php?page=wpw-participations&message=deleted'));
            exit;
        }

        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        $participations = WPW_DB::get_participations($per_page, $offset);
        $total = WPW_DB::count_participations();
        $total_pages = ceil($total / $per_page);

        $deleted = isset($_GET['message']) && $_GET['message'] === 'deleted';

        include WPW_PLUGIN_DIR . 'admin/views/participations-list.php';
    }
}
