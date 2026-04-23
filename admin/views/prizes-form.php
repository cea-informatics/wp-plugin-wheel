<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1>
        <?php echo $prize ? esc_html__('Modifier le lot', 'wp-plugin-wheel') : esc_html__('Ajouter un lot', 'wp-plugin-wheel'); ?>
    </h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes')); ?>">
        <?php wp_nonce_field('wpw_save_prize', 'wpw_nonce'); ?>

        <?php if ($prize): ?>
            <input type="hidden" name="prize_id" value="<?php echo esc_attr($prize->id); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="name"><?php esc_html_e('Nom', 'wp-plugin-wheel'); ?></label>
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
                    <label for="probability"><?php esc_html_e('Probabilité (poids)', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="number" id="probability" name="probability" min="1" class="small-text"
                           value="<?php echo $prize ? esc_attr($prize->probability) : '1'; ?>">
                    <p class="description"><?php esc_html_e('Plus la valeur est élevée, plus ce lot a de chances d\'être tiré.', 'wp-plugin-wheel'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="image_url"><?php esc_html_e('URL de l\'image', 'wp-plugin-wheel'); ?></label>
                </th>
                <td>
                    <input type="url" id="image_url" name="image_url" class="regular-text"
                           value="<?php echo $prize ? esc_attr($prize->image_url) : ''; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Actif', 'wp-plugin-wheel'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="active" value="1"
                               <?php checked(!$prize || $prize->active); ?>>
                        <?php esc_html_e('Ce lot est actif et peut être remporté', 'wp-plugin-wheel'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button($prize ? __('Mettre à jour', 'wp-plugin-wheel') : __('Ajouter', 'wp-plugin-wheel')); ?>
    </form>
</div>
