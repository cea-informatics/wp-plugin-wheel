<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Lots', 'wp-plugin-wheel'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes&action=new')); ?>" class="page-title-action">
        <?php esc_html_e('Ajouter un lot', 'wp-plugin-wheel'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                $msg = sanitize_text_field($_GET['message']);
                if ($msg === 'created') {
                    esc_html_e('Lot créé avec succès.', 'wp-plugin-wheel');
                } elseif ($msg === 'updated') {
                    esc_html_e('Lot mis à jour avec succès.', 'wp-plugin-wheel');
                } elseif ($msg === 'deleted') {
                    esc_html_e('Lot supprimé avec succès.', 'wp-plugin-wheel');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Nom', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Probabilité', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Actif', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Créé le', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Actions', 'wp-plugin-wheel'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($prizes)): ?>
                <tr>
                    <td colspan="6"><?php esc_html_e('Aucun lot trouvé.', 'wp-plugin-wheel'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($prizes as $prize): ?>
                    <tr>
                        <td><?php echo esc_html($prize->id); ?></td>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes&action=edit&id=' . $prize->id)); ?>">
                                    <?php echo esc_html($prize->name); ?>
                                </a>
                            </strong>
                        </td>
                        <td><?php echo esc_html($prize->probability); ?></td>
                        <td><?php echo $prize->active ? esc_html__('Oui', 'wp-plugin-wheel') : esc_html__('Non', 'wp-plugin-wheel'); ?></td>
                        <td><?php echo esc_html($prize->created_at); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes&action=edit&id=' . $prize->id)); ?>">
                                <?php esc_html_e('Modifier', 'wp-plugin-wheel'); ?>
                            </a>
                            |
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpw-prizes&action=delete&id=' . $prize->id), 'wpw_delete_prize_' . $prize->id)); ?>"
                               class="wpw-delete-link"
                               onclick="return confirm('<?php esc_attr_e('Supprimer ce lot ?', 'wp-plugin-wheel'); ?>');">
                                <?php esc_html_e('Supprimer', 'wp-plugin-wheel'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
