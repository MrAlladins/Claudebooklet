<?php
/**
 * Publik-specifik funktionalitet för pluginen
 *
 * @since      1.0.0
 */

class Thaibooklet_Public {

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
     * Registrerar stilar för den publika sidan
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, THAIBOOKLET_PLUGIN_URL . 'public/css/thaibooklet-public.css', array(), $this->version, 'all');
    }

    /**
     * Registrerar JavaScript för den publika sidan
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, THAIBOOKLET_PLUGIN_URL . 'public/js/thaibooklet-public.js', array('jquery'), $this->version, false);
        
        // Lägger till AJAX-url för JavaScript
        wp_localize_script($this->plugin_name, 'thaibooklet_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thaibooklet_public_nonce')
        ));
    }

    /**
     * Registrerar shortcodes
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('thaibooklet_digital', array($this, 'digital_booklet_shortcode'));
        add_shortcode('thaibooklet_company_login', array($this, 'company_login_shortcode'));
        add_shortcode('thaibooklet_verify_coupon', array($this, 'verify_coupon_shortcode'));
    }

    /**
     * Shortcode för att visa digital booklet
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attribut.
     * @return   string             HTML-output.
     */
    public function digital_booklet_shortcode($atts) {
        // Slå samman attributen med standardvärden
        $atts = shortcode_atts(array(
            'edition_id' => 0,
        ), $atts, 'thaibooklet_digital');
        
        // Om användaren inte är inloggad, visa inloggningsformulär
        if (!is_user_logged_in()) {
            ob_start();
            include THAIBOOKLET_PLUGIN_DIR . 'public/partials/thaibooklet-login-form.php';
            return ob_get_clean();
        }
        
        // Hämta aktuell användare
        $current_user = wp_get_current_user();
        
        // Hämta användarens booklet
        $booklet = $this->db->get_user_booklet($current_user->user_email, $atts['edition_id']);
        
        // Om användaren inte har ett booklet, visa registreringsformulär eller köpinformation
        if (!$booklet) {
            ob_start();
            include THAIBOOKLET_PLUGIN_DIR . 'public/partials/thaibooklet-register-form.php';
            return ob_get_clean();
        }
        
        // Hämta alla kuponger för detta booklet
        $coupons = $this->db->get_booklet_coupons($booklet->booklet_id);
        
        // Visa kuponger
        ob_start();
        include THAIBOOKLET_PLUGIN_DIR . 'public/partials/thaibooklet-digital-booklet.php';
        return ob_get_clean();
    }

    /**
     * Shortcode för företagsinloggning
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attribut.
     * @return   string             HTML-output.
     */
    public function company_login_shortcode($atts) {
        // Slå samman attributen med standardvärden
        $atts = shortcode_atts(array(), $atts, 'thaibooklet_company_login');
        
        // Om företagsanvändaren är inloggad, visa dashboard
        if (is_user_logged_in() && thaibooklet_is_company_user()) {
            ob_start();
            include THAIBOOKLET_PLUGIN_DIR . 'public/partials/thaibooklet-company-dashboard.php';
            return ob_get_clean();
        }
        
        // Annars visa inloggningsformulär
        ob_start();
        include THAIBOOKLET_PLUGIN_DIR . 'public/partials/thaibooklet-company-login.php';
        return ob_get_clean();
    }

    /**
     * Shortcode för kupongsverifiering
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attribut.
     * @return   string             HTML-output.
     */
    public function verify_coupon_shortcode($atts) {
        // Slå samman attributen med standardvärden
        $atts = shortcode_atts(array(), $atts, 'thaibooklet_verify_coupon');
        
        // Kontrollera att företagsanvändaren är inloggad
        if (!is_user_logged_in() || !thaibooklet_is_company_user()) {
            return __('You must be logged in as a company to verify coupons.', 'thaibooklet');
        }
        
        // Visa verifieringsformulär
        ob_start();
        include THAIBOOKLET_PLUGIN_DIR . 'public/partials/thaibooklet-verify-coupon.php';
        return ob_get_clean();
    }

    /**
     * AJAX-hanterare för inlösen av kupong
     */
    public function handle_redeem_coupon() {
        // Kontrollera nonce för säkerhet
        check_ajax_referer('thaibooklet_public_nonce', 'nonce');
        
        // Kontrollera att användaren är inloggad
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to redeem coupons.', 'thaibooklet')]);
            return;
        }
        
        // Validera och sanitera indata
        $coupon_id = isset($_POST['coupon_id']) ? intval($_POST['coupon_id']) : 0;
        
        // Kontrollera att kupongen finns och tillhör användaren
        $current_user = wp_get_current_user();
        $is_valid = $this->db->validate_user_coupon($coupon_id, $current_user->user_email);
        
        if (!$is_valid) {
            wp_send_json_error(['message' => __('Invalid coupon.', 'thaibooklet')]);
            return;
        }
        
        // Markera kupongen som använd
        $result = $this->db->mark_coupon_used($coupon_id, $current_user->user_email);
        
        if ($result) {
            wp_send_json_success(['message' => __('Coupon redeemed successfully!', 'thaibooklet')]);
        } else {
            wp_send_json_error(['message' => __('Error redeeming coupon.', 'thaibooklet')]);
        }
    }

    /**
     * AJAX-hanterare för validering av kupongkod (för företag)
     */
    public function handle_validate_code() {
        // Kontrollera nonce för säkerhet
        check_ajax_referer('thaibooklet_public_nonce', 'nonce');
        
        // Kontrollera att företaget är inloggat
        if (!is_user_logged_in() || !thaibooklet_is_company_user()) {
            wp_send_json_error(['message' => __('You must be logged in as a company to validate coupons.', 'thaibooklet')]);
            return;
        }
        
        // Validera och sanitera indata
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
        
        if (empty($coupon_code)) {
            wp_send_json_error(['message' => __('Please enter a coupon code.', 'thaibooklet')]);
            return;
        }
        
        // Hämta företags-ID för den inloggade användaren
        $company_id = thaibooklet_get_user_company_id();
        
        // Validera kupongen
        $coupon = $this->db->validate_company_coupon($coupon_code, $company_id);
        
        if (!$coupon) {
            wp_send_json_error(['message' => __('Invalid coupon code or coupon does not belong to your company.', 'thaibooklet')]);
            return;
        }
        
        if ($coupon->is_used) {
            wp_send_json_error(['message' => __('This coupon has already been redeemed.', 'thaibooklet')]);
            return;
        }
        
        // Markera kupongen som använd
        $current_user = wp_get_current_user();
        $result = $this->db->mark_coupon_used($coupon->coupon_id, $current_user->display_name, 'manual');
        
        if ($result) {
            // Uppdatera statistik
            $this->db->increment_redemption_count($coupon->coupon_type_id, $company_id);
            
            wp_send_json_success([
                'message' => __('Coupon validated and marked as used!', 'thaibooklet'),
                'coupon' => $coupon
            ]);
        } else {
            wp_send_json_error(['message' => __('Error validating coupon.', 'thaibooklet')]);
        }
    }
}