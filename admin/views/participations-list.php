<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php esc_html_e('Participations', 'wp-plugin-wheel'); ?></h1>

    <?php if (!empty($deleted)): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Participation supprimée.', 'wp-plugin-wheel'); ?></p></div>
    <?php endif; ?>

    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Utilisateur', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Lot remporté', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Date', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Heure', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Actions', 'wp-plugin-wheel'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($participations)): ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('Aucune participation trouvée.', 'wp-plugin-wheel'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($participations as $p): ?>
                    <tr>
                        <td><?php echo esc_html($p->id); ?></td>
                        <td><?php echo esc_html($p->user_name ? $p->user_name : __('Utilisateur supprimé', 'wp-plugin-wheel')); ?></td>
                        <td><?php echo esc_html($p->prize_name ? $p->prize_name : __('Lot supprimé', 'wp-plugin-wheel')); ?></td>
                        <td><?php echo esc_html($p->participation_date); ?></td>
                        <td><?php echo esc_html($p->participated_at); ?></td>
                        <td>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpw-participations&action=delete&id=' . $p->id), 'wpw_delete_participation_' . $p->id)); ?>"
                               class="wpw-delete-link"
                               onclick="return confirm('<?php esc_attr_e('Supprimer cette participation ?', 'wp-plugin-wheel'); ?>')">
                                <?php esc_html_e('Supprimer', 'wp-plugin-wheel'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total'     => $total_pages,
                    'current'   => $current_page,
                ));
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
