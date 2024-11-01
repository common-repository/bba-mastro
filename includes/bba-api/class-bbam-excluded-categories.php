<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBAAM_ExcludedCategories {
    private $page_id; 
    private $plugin_path;

    public function __construct($plugin_path) {
        $this->plugin_path = $plugin_path;
        $this->initialize();
    }

    private function initialize() {
        add_action('admin_menu', array($this, 'add_menu_link'));
        add_action('admin_post_update_excluded_categories', array($this, 'update_excluded_categories'));
        add_action('rest_api_init', array($this, 'register_custom_woo_route'));
    }

    public function add_menu_link() {
        $this->pageId = add_submenu_page (
            'woocommerce',
            __('BBA Excluded Product Categories', 'bba-mastro-excluded-categories'),
            __('BBA Excluded Product Categories', 'bba-mastro-excluded-categories'),
            'manage_woocommerce',
            'bba_admin_excluded_categories',
            array($this, 'render_page')
        );
    }

    public function update_excluded_categories() {
        $settings = get_option('woocommerce_bbamastro_rules_settings', null);
        $excluded_cat = isset($_POST['excluded_cat']) ? $_POST['excluded_cat'] : [];
        $settings['excluded_categories'] = implode(',', $excluded_cat);
        update_option('woocommerce_bbamastro_rules_settings', $settings);
        wp_redirect(admin_url('admin.php?page=bba_admin_excluded_categories'));
    }

    public function render_page() {
        include $this->plugin_path.'/admin/templates/wc-settings/bba_admin_product_categories.php';
    }

    public function register_custom_woo_route() {
        $api_namespace = 'wc/v';
        $api_version = '2';
        $namespace = $api_namespace.$api_version;

        register_rest_route(
            $namespace, 
            '/wc-excluded-categories',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_excluded_categories'),
                'permission_callback' => '__return_true'
            )
        );
    }

    public function get_excluded_categories($request) {
        $response = [
            'excluded_categories' => explode(",", BBAM_Utils::getConfig('excluded_categories'))
        ];
        return $response;
    }
}