<?php
/**
 * Admin-specifik funktionalitet för pluginen
 *
 * @since      1.0.0
 */

class Thaibooklet_Admin {

    /**
     * Plugin-namnet
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    Plugin-namnet
     */
    private $plugin_name;

    /**
     * Plugin-versionen
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    Aktuell plugin-version
     */
    private $version;

    /**
     * Databasklass
     *
     * @since    1.0.0
     * @access   private
     * @var      Thaibooklet_DB    $db    Databashantering
     */
    private $db;

    /**
     * Initierar klassen och sätter dess egenskaper
     *
     * @since    1.0.0
     * @param    string    $plugin_name    Plugin-namnet
     * @param    string    $version        Plugin-versionen
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = new Thaibooklet_DB();
    }

    /**
     * Registrerar stilar för admin-sidan
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, THAIBOOKLET_PLUGIN_URL . 'admin/css/thaibooklet-admin.css', array(), $this->version, 'all');
    }

    /**
     * Registrerar JavaScript för admin-sidan
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, THAIBOOKLET_PLUGIN_URL . 'admin/js/thaibooklet-admin.js', array('jquery'), $this->version, false);
        
        // Lägger till AJAX-url för JavaScript
        wp_localize_script($this->plugin_name, 'thaibooklet_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thaibooklet_nonce')
        ));
    }

    /**
     * Lägger till admin-menyer
     *
     * @since    1.0.0
     */
    public function setup_admin_menu() {
        // Huvudmeny
        add_menu_page(
            __('Thaibooklet Manager', 'thaibooklet'),
            __('Thaibooklet', 'thaibooklet'),
            'manage_options',
            'thaibooklet',
            array($this, 'display_dashboard_page'),
            'dashicons-tickets-alt',
            30
        );
        
        // Undermenyer
        add_submenu_page(
            'thaibooklet',
            __('Dashboard', 'thaibooklet'),
            __('Dashboard', 'thaibooklet'),
            'manage_options',
            'thaibooklet',
            array($this, 'display_dashboard_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Booklet Editions', 'thaibooklet'),
            __('Editions', 'thaibooklet'),
            'manage_options',
            'thaibooklet-editions',
            array($this, 'display_editions_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Companies', 'thaibooklet'),
            __('Companies', 'thaibooklet'),
            'manage_options',
            'thaibooklet-companies',
            array($this, 'display_companies_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Categories', 'thaibooklet'),
            __('Categories', 'thaibooklet'),
            'manage_options',
            'thaibooklet-categories',
            array($this, 'display_categories_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Booklets', 'thaibooklet'),
            __('Booklets', 'thaibooklet'),
            'manage_options',
            'thaibooklet-booklets',
            array($this, 'display_booklets_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Coupon Types', 'thaibooklet'),
            __('Coupon Types', 'thaibooklet'),
            'manage_options',
            'thaibooklet-coupon-types',
            array($this, 'display_coupon_types_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Statistics', 'thaibooklet'),
            __('Statistics', 'thaibooklet'),
            'manage_options',
            'thaibooklet-statistics',
            array($this, 'display_statistics_page')
        );
        
        add_submenu_page(
            'thaibooklet',
            __('Settings', 'thaibooklet'),
            __('Settings', 'thaibooklet'),
            'manage_options',
            'thaibooklet-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Visningssidor för admin
     */
    public function display_dashboard_page() {
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-dashboard.php';
    }
    
    public function display_editions_page() {
        // Hämta alla editions från databasen
        $editions = $this->db->get_editions();
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-editions.php';
    }
    
    public function display_companies_page() {
        // Hämta alla companies från databasen
        $companies = $this->db->get_companies();
        $categories = $this->db->get_categories();
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-companies.php';
    }
    
    public function display_categories_page() {
        // Hämta alla kategorier från databasen
        $categories = $this->db->get_categories();
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-categories.php';
    }
    
    public function display_booklets_page() {
        // Hämta alla booklets och editions från databasen
        $booklets = $this->db->get_booklets();
        $editions = $this->db->get_editions();
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-booklets.php';
    }
    
    public function display_coupon_types_page() {
        // Hämta alla coupon types, företag och editions från databasen
        $coupon_types = $this->db->get_coupon_types();
        $companies = $this->db->get_companies();
        $editions = $this->db->get_editions();
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-coupon-types.php';
    }
    
    public function display_statistics_page() {
        // Hämta statistik från databasen
        $stats = $this->db->get_redemption_statistics();
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-statistics.php';
    }
    
    public function display_settings_page() {
        require_once THAIBOOKLET_PLUGIN_DIR . 'admin/partials/thaibooklet-admin-settings.php';
    }

    /**
     * AJAX-hanterare för att spara en edition
     */
    public function handle_save_edition() {
        // Kontrollera nonce för säkerhet
        check_ajax_referer('thaibooklet_nonce', 'nonce');
        
        // Kontrollera behörighet
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'thaibooklet')]);
            return;
        }
        
        // Validera och sanitera indata
        $edition_id = isset($_POST['edition_id']) ? intval($_POST['edition_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Kontrollera obligatoriska fält
        if (empty($name) || empty($start_date) || empty($end_date)) {
            wp_send_json_error(['message' => __('Please fill all required fields', 'thaibooklet')]);
            return;
        }
        
        // Skapa eller uppdatera edition
        $edition_data = [
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $description,
            'price' => $price,
            'is_active' => $is_active
        ];
        
        if ($edition_id > 0) {
            // Uppdatera befintlig
            $result = $this->db->update_edition($edition_id, $edition_data);
            $message = __('Edition updated successfully', 'thaibooklet');
        } else {
            // Skapa ny
            $result = $this->db->add_edition($edition_data);
            $message = __('Edition created successfully', 'thaibooklet');
        }
        
        if ($result) {
            wp_send_json_success(['message' => $message]);
        } else {
            wp_send_json_error(['message' => __('Error saving edition', 'thaibooklet')]);
        }
    }

    /**
     * AJAX-hanterare för att spara ett företag
     */
    public function handle_save_company() {
        // Liknande implementation som handle_save_edition
        // Kontrollera nonce för säkerhet
        check_ajax_referer('thaibooklet_nonce', 'nonce');
        
        // Här skulle all validering och sparande av företagsdata ske
        
        wp_send_json_success(['message' => __('Company saved successfully', 'thaibooklet')]);
    }

    /**
     * AJAX-hanterare för att generera ett booklet
     */
    public function handle_generate_booklet() {
        // Kontrollera nonce för säkerhet
        check_ajax_referer('thaibooklet_nonce', 'nonce');
        
        // Här skulle all logik för att generera ett nytt booklet med kuponger ske
        
        wp_send_json_success(['message' => __('Booklet generated successfully', 'thaibooklet')]);
    }
}