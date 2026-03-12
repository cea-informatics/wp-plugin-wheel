<?php

if (!defined('ABSPATH')) exit;

class WPW_DB {

    /**
     * Create database tables.
     */
    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $prizes_table = $wpdb->prefix . 'wpw_prizes';
        $participations_table = $wpdb->prefix . 'wpw_participations';

        $sql = "CREATE TABLE $prizes_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL DEFAULT '',
            probability INT UNSIGNED NOT NULL DEFAULT 1,
            image_url VARCHAR(2083) NOT NULL DEFAULT '',
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;

        CREATE TABLE $participations_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            prize_id BIGINT UNSIGNED NOT NULL,
            participation_date DATE NOT NULL,
            participated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_date (user_id, participation_date)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /* -------------------------------------------------------------------------
     * Prizes CRUD
     * ---------------------------------------------------------------------- */

    /**
     * Get all prizes.
     */
    public static function get_prizes($active_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        if ($active_only) {
            return $wpdb->get_results("SELECT * FROM $table WHERE active = 1 ORDER BY id ASC");
        }

        return $wpdb->get_results("SELECT * FROM $table ORDER BY id ASC");
    }

    /**
     * Get a single prize by ID.
     */
    public static function get_prize($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    /**
     * Insert a new prize.
     */
    public static function insert_prize($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        $wpdb->insert($table, array(
            'name'        => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description']),
            'probability' => absint($data['probability']),
            'image_url'   => esc_url_raw($data['image_url']),
            'active'      => !empty($data['active']) ? 1 : 0,
        ), array('%s', '%s', '%d', '%s', '%d'));

        return $wpdb->insert_id;
    }

    /**
     * Update an existing prize.
     */
    public static function update_prize($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        return $wpdb->update($table, array(
            'name'        => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description']),
            'probability' => absint($data['probability']),
            'image_url'   => esc_url_raw($data['image_url']),
            'active'      => !empty($data['active']) ? 1 : 0,
        ), array('id' => $id), array('%s', '%s', '%d', '%s', '%d'), array('%d'));
    }

    /**
     * Delete a prize.
     */
    public static function delete_prize($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    /* -------------------------------------------------------------------------
     * Participations
     * ---------------------------------------------------------------------- */

    /**
     * Check if a user has already participated today.
     */
    public static function has_participated_today($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND participation_date = %s",
            $user_id,
            current_time('Y-m-d')
        ));
    }

    /**
     * Record a participation.
     */
    public static function record_participation($user_id, $prize_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return $wpdb->insert($table, array(
            'user_id'            => $user_id,
            'prize_id'           => $prize_id,
            'participation_date' => current_time('Y-m-d'),
            'participated_at'    => current_time('mysql'),
        ), array('%d', '%d', '%s', '%s'));
    }

    /**
     * Get participations with pagination.
     */
    public static function get_participations($per_page = 20, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';
        $prizes_table = $wpdb->prefix . 'wpw_prizes';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, pr.name AS prize_name, u.display_name AS user_name
             FROM $table p
             LEFT JOIN $prizes_table pr ON p.prize_id = pr.id
             LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
             ORDER BY p.participated_at DESC
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
    }

    /**
     * Delete a participation by ID.
     */
    public static function delete_participation($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    /**
     * Count total participations.
     */
    public static function count_participations() {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    /* -------------------------------------------------------------------------
     * Random prize selection (weighted)
     * ---------------------------------------------------------------------- */

    /**
     * Pick a random prize using weighted probability.
     */
    public static function pick_random_prize() {
        $prizes = self::get_prizes(true);

        if (empty($prizes)) {
            return null;
        }

        $total_weight = 0;
        foreach ($prizes as $prize) {
            $total_weight += $prize->probability;
        }

        $random = mt_rand(1, $total_weight);
        $current = 0;

        foreach ($prizes as $prize) {
            $current += $prize->probability;
            if ($random <= $current) {
                return $prize;
            }
        }

        return $prizes[0];
    }
}
