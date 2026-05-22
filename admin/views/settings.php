<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php esc_html_e('Wheel Settings', 'wp-plugin-wheel'); ?></h1>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'wp-plugin-wheel'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpw-settings')); ?>">
        <?php wp_nonce_field('wpw_save_settings', 'wpw_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="daily_limit"><?php esc_html_e('Daily prize limit', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="number" id="daily_limit" name="daily_limit" min="1" class="small-text"
                           value="<?php echo esc_attr($daily_limit); ?>">
                    <p class="description"><?php esc_html_e('Maximum number of prizes distributed globally per day. Once reached, all subsequent spins are losses.', 'wp-plugin-wheel'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="daily_attempts"><?php esc_html_e('Attempts per user per day', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="number" id="daily_attempts" name="daily_attempts" min="1" class="small-text"
                           value="<?php echo esc_attr($daily_attempts); ?>">
                    <p class="description"><?php esc_html_e('Number of spins each user is allowed per day.', 'wp-plugin-wheel'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="lose_probability"><?php esc_html_e('Lose probability (weight)', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="number" id="lose_probability" name="lose_probability" min="0" class="small-text"
                           value="<?php echo esc_attr($lose_probability); ?>">
                    <p class="description">
                        <?php esc_html_e('Weight assigned to losing. Set to 0 to disable losing (all spins win a prize). The effective lose rate depends on the total prize weight.', 'wp-plugin-wheel'); ?>
                        <?php if ($lose_probability > 0):
                            global $wpdb;
                            $prize_weight = (int) $wpdb->get_var("SELECT SUM(probability) FROM {$wpdb->prefix}wpw_prizes WHERE active = 1 AND (stock IS NULL OR stock > 0)");
                            $total = $prize_weight + $lose_probability;
                            $pct   = $total > 0 ? round($lose_probability / $total * 100) : 0;
                        ?>
                            <strong><?php printf(esc_html__('Current estimated lose rate: %d%%', 'wp-plugin-wheel'), $pct); ?></strong>
                        <?php endif; ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Settings', 'wp-plugin-wheel')); ?>
    </form>
</div>
