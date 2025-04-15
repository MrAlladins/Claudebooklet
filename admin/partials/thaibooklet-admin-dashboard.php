<?php
/**
 * Admin dashboard för pluginen
 *
 * @since      1.0.0
 */

// Skydda mot direkt åtkomst
if (!defined('WPINC')) {
    die;
}

// Hämta statistik för aktiva editionen
$active_edition = $this->db->get_active_edition();
$current_stats = $active_edition ? $this->db->get_edition_statistics($active_edition->edition_id) : null;

// Totalt antal booklets
$total_booklets = $this->db->get_total_booklets();
$digital_booklets = $this->db->get_total_booklets('digital');
$physical_booklets = $this->db->get_total_booklets('physical');

// Populära kuponger 
$popular_coupons = $this->db->get_most_popular_coupons(5);

// Senaste aktiviteter
$recent_redemptions = $this->db->get_recent_redemptions(10);
?>

<div class="wrap thaibooklet-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="thaibooklet-dashboard">
        <div class="thaibooklet-dashboard-row">
            <div class="thaibooklet-card">
                <h2><span class="dashicons dashicons-tickets-alt"></span> <?php _e('Booklets Overview', 'thaibooklet'); ?></h2>
                <div class="thaibooklet-stats-grid">
                    <div class="thaibooklet-stat-item">
                        <span class="thaibooklet-stat-number"><?php echo esc_html($total_booklets); ?></span>
                        <span class="thaibooklet-stat-title"><?php _e('Total Booklets', 'thaibooklet'); ?></span>
                    </div>
                    <div class="thaibooklet-stat-item">
                        <span class="thaibooklet-stat-number"><?php echo esc_html($digital_booklets); ?></span>
                        <span class="thaibooklet-stat-title"><?php _e('Digital Booklets', 'thaibooklet'); ?></span>
                    </div>
                    <div class="thaibooklet-stat-item">
                        <span class="thaibooklet-stat-number"><?php echo esc_html($physical_booklets); ?></span>
                        <span class="thaibooklet-stat-title"><?php _e('Physical Booklets', 'thaibooklet'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="thaibooklet-card">
                <h2>
                    <span class="dashicons dashicons-chart-bar"></span> 
                    <?php
                    if ($active_edition) {
                        printf(__('Current Edition: %s', 'thaibooklet'), esc_html($active_edition->name));
                    } else {
                        _e('No Active Edition', 'thaibooklet');
                    }
                    ?>
                </h2>
                
                <?php if ($active_edition && $current_stats): ?>
                    <div class="thaibooklet-stats-grid">
                        <div class="thaibooklet-stat-item">
                            <span class="thaibooklet-stat-number"><?php echo esc_html($current_stats->total_booklets); ?></span>
                            <span class="thaibooklet-stat-title"><?php _e('Edition Booklets', 'thaibooklet'); ?></span>
                        </div>
                        <div class="thaibooklet-stat-item">
                            <span class="thaibooklet-stat-number"><?php echo esc_html($current_stats->total_redemptions); ?></span>
                            <span class="thaibooklet-stat-title"><?php _e('Total Redemptions', 'thaibooklet'); ?></span>
                        </div>
                        <div class="thaibooklet-stat-item">
                            <span class="thaibooklet-stat-number"><?php echo esc_html($current_stats->active_companies); ?></span>
                            <span class="thaibooklet-stat-title"><?php _e('Active Companies', 'thaibooklet'); ?></span>
                        </div>
                    </div>
                    
                    <div class="thaibooklet-stats-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-statistics&edition_id=' . $active_edition->edition_id)); ?>" class="button button-secondary">
                            <?php _e('View Detailed Statistics', 'thaibooklet'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <p class="thaibooklet-no-data">
                        <?php _e('No active edition found. Please create an edition to see statistics.', 'thaibooklet'); ?>
                    </p>
                    
                    <div class="thaibooklet-stats-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-editions')); ?>" class="button button-primary">
                            <?php _e('Create Edition', 'thaibooklet'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="thaibooklet-dashboard-row">
            <div class="thaibooklet-card">
                <h2><span class="dashicons dashicons-star-filled"></span> <?php _e('Popular Coupons', 'thaibooklet'); ?></h2>
                
                <?php if (!empty($popular_coupons)): ?>
                    <table class="thaibooklet-table">
                        <thead>
                            <tr>
                                <th><?php _e('Coupon', 'thaibooklet'); ?></th>
                                <th><?php _e('Company', 'thaibooklet'); ?></th>
                                <th><?php _e('Redemptions', 'thaibooklet'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_coupons as $coupon): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($coupon->title); ?></strong><br>
                                        <small><?php echo esc_html($coupon->edition_name); ?></small>
                                    </td>
                                    <td><?php echo esc_html($coupon->company_name); ?></td>
                                    <td class="thaibooklet-count-column"><?php echo esc_html($coupon->redemptions_count); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="thaibooklet-no-data">
                        <?php _e('No coupon redemption data available yet.', 'thaibooklet'); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="thaibooklet-card">
                <h2><span class="dashicons dashicons-clock"></span> <?php _e('Recent Activity', 'thaibooklet'); ?></h2>
                
                <?php if (!empty($recent_redemptions)): ?>
                    <table class="thaibooklet-table">
                        <thead>
                            <tr>
                                <th><?php _e('Coupon', 'thaibooklet'); ?></th>
                                <th><?php _e('User', 'thaibooklet'); ?></th>
                                <th><?php _e('Date', 'thaibooklet'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_redemptions as $redemption): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($redemption->title); ?></strong><br>
                                        <small><?php echo esc_html($redemption->company_name); ?></small>
                                    </td>
                                    <td><?php echo esc_html($redemption->used_by); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($redemption->used_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="thaibooklet-no-data">
                        <?php _e('No recent activity found.', 'thaibooklet'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="thaibooklet-dashboard-row">
            <div class="thaibooklet-card">
                <h2><span class="dashicons dashicons-admin-tools"></span> <?php _e('Quick Actions', 'thaibooklet'); ?></h2>
                
                <div class="thaibooklet-quick-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-booklets&action=new')); ?>" class="thaibooklet-action-button">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Create Booklet', 'thaibooklet'); ?>
                    </a>
                    
                    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-companies&action=new')); ?>" class="thaibooklet-action-button">
                        <span class="dashicons dashicons-building"></span>
                        <?php _e('Add Company', 'thaibooklet'); ?>
                    </a>
                    
                    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-coupon-types&action=new')); ?>" class="thaibooklet-action-button">
                        <span class="dashicons dashicons-tag"></span>
                        <?php _e('Add Coupon Type', 'thaibooklet'); ?>
                    </a>
                    
                    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-settings')); ?>" class="thaibooklet-action-button">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php _e('Settings', 'thaibooklet'); ?>
                    </a>
                </div>
            </div>
            
            <div class="thaibooklet-card">
                <h2><span class="dashicons dashicons-info"></span> <?php _e('System Info', 'thaibooklet'); ?></h2>
                
                <table class="thaibooklet-system-info">
                    <tr>
                        <th><?php _e('Plugin Version:', 'thaibooklet'); ?></th>
                        <td><?php echo esc_html(THAIBOOKLET_VERSION); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Database Version:', 'thaibooklet'); ?></th>
                        <td><?php echo esc_html(get_option('thaibooklet_db_version', '1.0.0')); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('WordPress Version:', 'thaibooklet'); ?></th>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('PHP Version:', 'thaibooklet'); ?></th>
                        <td><?php echo esc_html(phpversion()); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Active Editions:', 'thaibooklet'); ?></th>
                        <td><?php echo esc_html($this->db->get_active_editions_count()); ?></td>
                    </tr>
                </table>
                
                <div class="thaibooklet-stats-footer">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=thaibooklet-settings&tab=system')); ?>" class="button button-secondary">
                        <?php _e('View System Status', 'thaibooklet'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>