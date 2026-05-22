<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Prizes', 'wp-plugin-wheel'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes&action=new')); ?>" class="page-title-action">
        <?php esc_html_e('Add Prize', 'wp-plugin-wheel'); ?>
    </a>
    <hr class="wp-header-end">

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                $msg = sanitize_text_field($_GET['message']);
                if ($msg === 'created') {
                    esc_html_e('Prize created successfully.', 'wp-plugin-wheel');
                } elseif ($msg === 'updated') {
                    esc_html_e('Prize updated successfully.', 'wp-plugin-wheel');
                } elseif ($msg === 'deleted') {
                    esc_html_e('Prize deleted successfully.', 'wp-plugin-wheel');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Name', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Probability', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Stock', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Active', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Created', 'wp-plugin-wheel'); ?></th>
                <th><?php esc_html_e('Actions', 'wp-plugin-wheel'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($prizes)): ?>
                <tr>
                    <td colspan="7"><?php esc_html_e('No prizes found.', 'wp-plugin-wheel'); ?></td>
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
                        <td>
                            <?php if ($prize->stock === null): ?>
                                <span title="<?php esc_attr_e('Unlimited', 'wp-plugin-wheel'); ?>">∞</span>
                            <?php elseif ((int) $prize->stock === 0): ?>
                                <span style="color:#d63638;"><?php esc_html_e('Out of stock', 'wp-plugin-wheel'); ?></span>
                            <?php else: ?>
                                <?php echo esc_html($prize->stock); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $prize->active ? esc_html__('Yes', 'wp-plugin-wheel') : esc_html__('No', 'wp-plugin-wheel'); ?></td>
                        <td><?php echo esc_html($prize->created_at); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpw-prizes&action=edit&id=' . $prize->id)); ?>">
                                <?php esc_html_e('Edit', 'wp-plugin-wheel'); ?>
                            </a>
                            |
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wpw-prizes&action=delete&id=' . $prize->id), 'wpw_delete_prize_' . $prize->id)); ?>"
                               class="wpw-delete-link"
                               onclick="return confirm('<?php esc_attr_e('Delete this prize?', 'wp-plugin-wheel'); ?>');">
                                <?php esc_html_e('Delete', 'wp-plugin-wheel'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
