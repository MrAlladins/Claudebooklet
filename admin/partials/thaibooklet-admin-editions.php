<?php
/**
 * Admin-sida för hantering av booklet editions
 *
 * @since      1.0.0
 */

// Skydda mot direkt åtkomst
if (!defined('WPINC')) {
    die;
}

// Kontrollera om det är en enskild edition-visning/redigering
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$edition_id = isset($_GET['edition_id']) ? intval($_GET['edition_id']) : 0;

if ($action === 'edit' && $edition_id > 0) {
    $edition = $this->db->get_edition($edition_id);
    include THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-edition-form.php';
    return;
} elseif ($action === 'new') {
    $edition = null;
    include THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-edition-form.php';
    return;
}
?>

<div class="wrap thaibooklet-admin">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-editions&action=new')); ?>" class="page-title-action">
        <?php _e('Add New', 'thaibooklet'); ?>
    </a>
    <hr class="wp-header-end">
    
    <?php
    // Visa eventuella meddelanden
    if (isset($_GET['message']) && $_GET['message'] === '1') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Edition updated successfully.', 'thaibooklet') . '</p></div>';
    } elseif (isset($_GET['message']) && $_GET['message'] === '2') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Edition added successfully.', 'thaibooklet') . '</p></div>';
    } elseif (isset($_GET['message']) && $_GET['message'] === '3') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Edition deleted successfully.', 'thaibooklet') . '</p></div>';
    }
    ?>
    
    <div class="thaibooklet-admin-content">
        <?php if (empty($editions)) : ?>
            <div class="thaibooklet-no-items">
                <p><?php _e('No editions found.', 'thaibooklet'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-editions&action=new')); ?>" class="button button-primary">
                    <?php _e('Create First Edition', 'thaibooklet'); ?>
                </a>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped thaibooklet-table">
                <thead>
                    <tr>
                        <th scope="col" class="column-primary"><?php _e('Name', 'thaibooklet'); ?></th>
                        <th scope="col"><?php _e('Validity Period', 'thaibooklet'); ?></th>
                        <th scope="col"><?php _e('Price', 'thaibooklet'); ?></th>
                        <th scope="col"><?php _e('Booklets', 'thaibooklet'); ?></th>
                        <th scope="col"><?php _e('Status', 'thaibooklet'); ?></th>
                        <th scope="col"><?php _e('Actions', 'thaibooklet'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($editions as $edition) : 
                        // Beräkna om editionen är aktiv baserat på datum
                        $now = current_time('mysql');
                        $start_date = $edition->start_date;
                        $end_date = $edition->end_date;
                        $date_active = ($start_date <= $now && $end_date >= $now);
                        $status_active = (bool) $edition->is_active && $date_active;
                        
                        // Hämta antal booklets för denna edition
                        $booklets_count = $this->db->get_edition_booklets_count($edition->edition_id);
                    ?>
                        <tr>
                            <td class="column-primary">
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-editions&action=edit&edition_id=' . $edition->edition_id)); ?>">
                                        <?php echo esc_html($edition->name); ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-editions&action=edit&edition_id=' . $edition->edition_id)); ?>">
                                            <?php _e('Edit', 'thaibooklet'); ?>
                                        </a> | 
                                    </span>
                                    <span class="duplicate">
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=thaibooklet-editions&action=duplicate&edition_id=' . $edition->edition_id), 'duplicate_edition_' . $edition->edition_id)); ?>">
                                            <?php _e('Duplicate', 'thaibooklet'); ?>
                                        </a> | 
                                    </span>
                                    <span class="trash">
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=thaibooklet-editions&action=delete&edition_id=' . $edition->edition_id), 'delete_edition_' . $edition->edition_id)); ?>" class="submitdelete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this edition?', 'thaibooklet'); ?>');">
                                            <?php _e('Delete', 'thaibooklet'); ?>
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php 
                                echo esc_html(date_i18n(get_option('date_format'), strtotime($edition->start_date)));
                                echo ' &mdash; ';
                                echo esc_html(date_i18n(get_option('date_format'), strtotime($edition->end_date)));
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($edition->price > 0) {
                                    echo esc_html(number_format_i18n($edition->price, 2)) . ' ' . get_option('thaibooklet_currency', 'THB');
                                } else {
                                    _e('Free', 'thaibooklet');
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-booklets&edition_id=' . $edition->edition_id)); ?>">
                                    <?php echo esc_html($booklets_count); ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($status_active) : ?>
                                    <span class="thaibooklet-status thaibooklet-status-active"><?php _e('Active', 'thaibooklet'); ?></span>
                                <?php elseif (!$edition->is_active) : ?>
                                    <span class="thaibooklet-status thaibooklet-status-inactive"><?php _e('Inactive', 'thaibooklet'); ?></span>
                                <?php elseif ($start_date > $now) : ?>
                                    <span class="thaibooklet-status thaibooklet-status-upcoming"><?php _e('Upcoming', 'thaibooklet'); ?></span>
                                <?php else : ?>
                                    <span class="thaibooklet-status thaibooklet-status-expired"><?php _e('Expired', 'thaibooklet'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="thaibooklet-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-statistics&edition_id=' . $edition->edition_id)); ?>" class="button button-small">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                </a>
                                
                                <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-booklets&action=new&edition_id=' . $edition->edition_id)); ?>" class="button button-small">
                                    <span class="dashicons dashicons-plus"></span>
                                </a>
                                
                                <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-coupon-types&edition_id=' . $edition->edition_id)); ?>" class="button button-small">
                                    <span class="dashicons dashicons-tickets-alt"></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>