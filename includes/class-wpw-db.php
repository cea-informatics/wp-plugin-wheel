<?php

if (!defined('ABSPATH')) exit;

class WPW_DB {

    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $prizes_table         = $wpdb->prefix . 'wpw_prizes';
        $participations_table = $wpdb->prefix . 'wpw_participations';

        $sql = "CREATE TABLE $prizes_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL DEFAULT '',
            probability INT UNSIGNED NOT NULL DEFAULT 1,
            stock INT UNSIGNED NULL DEFAULT NULL,
            image_url VARCHAR(2083) NOT NULL DEFAULT '',
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;

        CREATE TABLE $participations_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            prize_id BIGINT UNSIGNED NULL DEFAULT NULL,
            won TINYINT(1) NOT NULL DEFAULT 1,
            participation_date DATE NOT NULL,
            participated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        self::maybe_migrate();
    }

    private static function maybe_migrate() {
        global $wpdb;

        if (version_compare(get_option('wpw_db_version', '1.0'), '2.0', '>=')) {
            return;
        }

        $prizes_table         = $wpdb->prefix . 'wpw_prizes';
        $participations_table = $wpdb->prefix . 'wpw_participations';

        $prize_cols = $wpdb->get_col("DESCRIBE $prizes_table");
        if (!in_array('stock', $prize_cols, true)) {
            $wpdb->query("ALTER TABLE $prizes_table ADD COLUMN stock INT UNSIGNED NULL DEFAULT NULL AFTER active");
        }

        $part_cols = $wpdb->get_col("DESCRIBE $participations_table");
        if (!in_array('won', $part_cols, true)) {
            $wpdb->query("ALTER TABLE $participations_table ADD COLUMN won TINYINT(1) NOT NULL DEFAULT 1 AFTER prize_id");
        }

        $col_info = $wpdb->get_row("SHOW COLUMNS FROM $participations_table WHERE Field = 'prize_id'");
        if ($col_info && $col_info->Null === 'NO') {
            $wpdb->query("ALTER TABLE $participations_table MODIFY COLUMN prize_id BIGINT UNSIGNED NULL DEFAULT NULL");
        }

        $indexes = $wpdb->get_results("SHOW INDEX FROM $participations_table WHERE Key_name = 'user_date'");
        if (!empty($indexes)) {
            $wpdb->query("ALTER TABLE $participations_table DROP INDEX user_date");
        }

        update_option('wpw_db_version', '2.0');
    }

    /* -------------------------------------------------------------------------
     * Prizes CRUD
     * ---------------------------------------------------------------------- */

    public static function get_prizes($active_only = false) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        if ($active_only) {
            return $wpdb->get_results("SELECT * FROM $table WHERE active = 1 ORDER BY id ASC");
        }

        return $wpdb->get_results("SELECT * FROM $table ORDER BY id ASC");
    }

    public static function get_prize($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function insert_prize($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        $name        = sanitize_text_field($data['name']);
        $description = sanitize_textarea_field($data['description']);
        $probability = absint($data['probability']);
        $image_url   = esc_url_raw($data['image_url']);
        $active      = !empty($data['active']) ? 1 : 0;
        $stock       = (isset($data['stock']) && $data['stock'] !== '') ? absint($data['stock']) : null;

        if ($stock === null) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (name, description, probability, stock, image_url, active) VALUES (%s, %s, %d, NULL, %s, %d)",
                $name, $description, $probability, $image_url, $active
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (name, description, probability, stock, image_url, active) VALUES (%s, %s, %d, %d, %s, %d)",
                $name, $description, $probability, $stock, $image_url, $active
            ));
        }

        return $wpdb->insert_id;
    }

    public static function update_prize($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        $name        = sanitize_text_field($data['name']);
        $description = sanitize_textarea_field($data['description']);
        $probability = absint($data['probability']);
        $image_url   = esc_url_raw($data['image_url']);
        $active      = !empty($data['active']) ? 1 : 0;
        $stock       = (isset($data['stock']) && $data['stock'] !== '') ? absint($data['stock']) : null;

        if ($stock === null) {
            return $wpdb->query($wpdb->prepare(
                "UPDATE $table SET name=%s, description=%s, probability=%d, stock=NULL, image_url=%s, active=%d WHERE id=%d",
                $name, $description, $probability, $image_url, $active, $id
            ));
        }

        return $wpdb->query($wpdb->prepare(
            "UPDATE $table SET name=%s, description=%s, probability=%d, stock=%d, image_url=%s, active=%d WHERE id=%d",
            $name, $description, $probability, $stock, $image_url, $active, $id
        ));
    }

    public static function delete_prize($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    private static function decrement_stock($prize_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET stock = stock - 1 WHERE id = %d AND stock IS NOT NULL AND stock > 0",
            $prize_id
        ));
    }

    /* -------------------------------------------------------------------------
     * Participations
     * ---------------------------------------------------------------------- */

    public static function count_today_attempts($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND participation_date = %s",
            $user_id,
            current_time('Y-m-d')
        ));
    }

    public static function count_today_wins() {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE won = 1 AND participation_date = %s",
            current_time('Y-m-d')
        ));
    }

    public static function record_participation($user_id, $prize_id, $won) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        if ($prize_id === null) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (user_id, prize_id, won, participation_date, participated_at) VALUES (%d, NULL, 0, %s, %s)",
                $user_id, current_time('Y-m-d'), current_time('mysql')
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (user_id, prize_id, won, participation_date, participated_at) VALUES (%d, %d, %d, %s, %s)",
                $user_id, $prize_id, $won ? 1 : 0, current_time('Y-m-d'), current_time('mysql')
            ));
            if ($won) {
                self::decrement_stock($prize_id);
            }
        }

        return $wpdb->insert_id;
    }

    public static function get_participations($per_page = 20, $offset = 0) {
        global $wpdb;
        $table        = $wpdb->prefix . 'wpw_participations';
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

    public static function delete_participation($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    public static function count_participations() {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_participations';

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    /* -------------------------------------------------------------------------
     * Random prize selection (weighted, respects stock)
     * ---------------------------------------------------------------------- */

    public static function pick_random_prize() {
        global $wpdb;
        $table = $wpdb->prefix . 'wpw_prizes';

        $prizes = $wpdb->get_results(
            "SELECT * FROM $table WHERE active = 1 AND (stock IS NULL OR stock > 0) ORDER BY id ASC"
        );

        if (empty($prizes)) {
            return null;
        }

        $lose_weight  = (int) get_option('wpw_lose_probability', 0);
        $total_weight = $lose_weight;

        foreach ($prizes as $prize) {
            $total_weight += (int) $prize->probability;
        }

        if ($total_weight <= 0) {
            return $prizes[0];
        }

        $random = mt_rand(1, $total_weight);

        if ($random <= $lose_weight) {
            return null;
        }

        $current = $lose_weight;
        foreach ($prizes as $prize) {
            $current += (int) $prize->probability;
            if ($random <= $current) {
                return $prize;
            }
        }

        return $prizes[0];
    }
}
