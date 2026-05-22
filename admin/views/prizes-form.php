<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1>
        <?php echo $prize ? esc_html__('Edit Prize', 'wp-plugin-wheel') : esc_html__('Add Prize', 'wp-plugin-wheel'); ?>
    </h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes')); ?>">
        <?php wp_nonce_field('wpw_save_prize', 'wpw_nonce'); ?>

        <?php if ($prize): ?>
            <input type="hidden" name="prize_id" value="<?php echo esc_attr($prize->id); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="name"><?php esc_html_e('Name', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="text" id="name" name="name" class="regular-text" required
                           value="<?php echo $prize ? esc_attr($prize->name) : ''; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="description"><?php esc_html_e('Description', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <textarea id="description" name="description" rows="4" class="large-text"><?php echo $prize ? esc_textarea($prize->description) : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="probability"><?php esc_html_e('Probability (weight)', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="number" id="probability" name="probability" min="1" class="small-text"
                           value="<?php echo $prize ? esc_attr($prize->probability) : '1'; ?>">
                    <p class="description"><?php esc_html_e('The higher the value, the more likely this prize will be drawn.', 'wp-plugin-wheel'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="stock"><?php esc_html_e('Stock', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="number" id="stock" name="stock" min="0" class="small-text"
                           value="<?php echo ($prize && $prize->stock !== null) ? esc_attr($prize->stock) : ''; ?>">
                    <p class="description"><?php esc_html_e('Leave empty for unlimited stock. Set to 0 to temporarily disable this prize.', 'wp-plugin-wheel'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="image_url"><?php esc_html_e('Image URL', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="url" id="image_url" name="image_url" class="regular-text"
                           value="<?php echo $prize ? esc_attr($prize->image_url) : ''; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Active', 'wp-plugin-wheel'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="active" value="1"
                               <?php checked(!$prize || $prize->active); ?>>
                        <?php esc_html_e('This prize is active and can be won', 'wp-plugin-wheel'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button($prize ? __('Update', 'wp-plugin-wheel') : __('Add', 'wp-plugin-wheel')); ?>
    </form>
</div>
