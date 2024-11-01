<?php

/**
 * Plugin Name: BBA Mastro
 * Description: Multi Carrier Shipping and Logistics Technology able to seamlessly integrate into your Woo cart.
 * Version: 2.4.12.9
 * Author: BBA Mastro
 * Author URI: https://bbamastro.com
 * @package BBA Mastro
 * @category Core
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('BBAMastro')) {
  final class BBAMastro
  {
    public $version = '2.4.12.9';
    public static $bbamTrackingConsignmentInstance;
    public static $bbam_excluded_categories_ins;
    protected static $usInstance = null;

    public function __construct()
    {
      require_once "includes/bba-api/bbamastro-token.php";
      require_once "includes/bba-api/class-bbam-utils.php";
      $this->initialize();
    }

    public static function getInstance()
    {
      if (is_null(self::$usInstance)) {
        self::$usInstance = new self();
      }
      return self::$usInstance;
    }

    private function initialize()
    {
      $this->initializeConstants();
      add_action('wp_loaded', ['BBAMastro', 'onWordpressLoaded']);
      add_action('plugins_loaded', ['BBAMastro', 'onPluginLoaded']);
      register_activation_hook(__FILE__, ['BBAMastro', 'onPluginActivate']);
    }

    public static function onPluginActivate()
    {
      if (BBAM_Utils::woocommerceEnabled() && is_admin()) {
        BBAM_Utils::addTaxonomy("manufacturer");
      }
    }

    public static function onWordpressLoaded()
    {
      if (BBAM_Utils::woocommerceEnabled() && is_admin()) {
        self::includeAdminJsScripts();
        BBAM_Utils::addTerms('manufacturer');
      }

      self::includePublicJsScripts();
      self::includePublicCSSScript();
    }

    private static function includeAdminJsScripts()
    {
      $countries = new BBAM_Country();
      wp_register_script('bbamastro-api', self::pluginURL() . '/admin/js/shipping-rules.js', ['jquery', 'select2'], BBAMASTRO_VERSION);
      wp_localize_script('bbamastro-api', 'bbamastro', [
        'countries'  => $countries->getOptions(true),
        'settings'   => [
          'country'  => BBAM_Utils::getConfig('warehouse_country'),
          'postcode' => BBAM_Utils::getConfig('warehouse_postcode')
        ]
      ]);
      wp_enqueue_script('bbamastro-api');
    }

    private static function includePublicJsScripts()
    {
      wp_register_script('bbamastro-cart', self::pluginURL() . '/public/js/bba-cart.js', ['jquery', 'select2'], BBAMASTRO_VERSION);
      wp_enqueue_script('bbamastro-cart');
    }

    private static function includePublicCSSScript()
    {
      wp_enqueue_style('bba-cart-css', self::pluginURL() . '/public/css/bba-cart.css');
    }

    private static function pluginURL()
    {
      return untrailingslashit(plugins_url('/', __FILE__));
    }

    private static function pluginPath()
    {
      return untrailingslashit(plugin_dir_path(__FILE__));
    }

    public static function onPluginLoaded()
    {
      if (BBAM_Utils::woocommerceEnabled()) {
        BBAM_Utils::includeBbaClass();

        add_filter('woocommerce_shipping_methods', ['BBAMastro', 'shippingMethods']);
        add_action('woocommerce_webhook_payload', ['BBAM_Utils', 'generateWebhookPayload']);
        add_filter('woocommerce_rest_prepare_product_object', ['BBAM_Utils', 'prepareProducts']);
        add_filter('woocommerce_default_address_fields', ['BBAMastro', 'reorderCheckoutFields']);
        add_filter('woocommerce_package_rates', ['BBAMastro', 'hideFlatRate']);

        self::$bbamTrackingConsignmentInstance = new BBAM_TrackingIdAndConsignmentIdFields(self::pluginPath());
        self::$bbam_excluded_categories_ins = new BBAAM_ExcludedCategories(self::pluginPath());

        add_action('woocommerce_new_order', ['BBAMastro', 'addGoodsCheckToOrder']);
        add_action('woocommerce_thankyou', ['BBAMastro', 'removeGoodsCheckSession'], 10, 1);

        if (BBAM_Utils::getConfig('quote_notes')) {
          add_action('woocommerce_cart_totals_before_shipping', ['BBAMastro', 'addNoteOnCartSubTotalsSection']);
          add_action('woocommerce_review_order_before_shipping', ['BBAMastro', 'addNoteOnCartSubTotalsSection']);
        }

        if (BBAM_Utils::getConfig('round_up_quote_values') == 'yes') {
          add_filter('woocommerce_price_trim_zeros', '__return_true');
        }

        $tailgate_delivery = BBAM_Utils::getConfig('show_tailgate_delivery');
        $residential_delivery = BBAM_Utils::getConfig('show_residential_delivery');
        
        // todo: need to fix this one here...
        if ($tailgate_delivery === 'yes' || $residential_delivery === 'yes') {
          add_action('woocommerce_cart_totals_before_shipping', ['BBAMastro', 'addLoadingIndicator']);
          add_action('woocommerce_cart_totals_before_shipping', ['BBAMastro', 'add_goods_check_in_cart_page']);

          add_action('woocommerce_review_order_before_shipping', ['BBAMastro', 'addLoadingIndicator']);
          add_action('woocommerce_review_order_before_shipping', ['BBAMastro', 'add_goods_check_in_checkout_page']);

          add_action('wp_ajax_update_cart', ['BBAMastro', 'updateCartWithGoodsCheck']);
          add_action('wp_ajax_nopriv_update_cart', ['BBAMastro', 'updateCartWithGoodsCheck']);
        }        

        add_action('init', ['BBAMastro', 'check_and_enable_webhook']);
      }
    }

    public static function check_and_enable_webhook() 
    {
      $data_store = WC_Data_Store::load('webhook');
      $webhooks = $data_store->search_webhooks(['status' => 'disabled']);
      $_items = array_map('wc_get_webhook', $webhooks);
      
      foreach ($_items as $webhook) {
        $str_to_check = 'bbamastro';
        $webhook_delivery_url = $webhook->get_delivery_url();
        if (strpos($webhook_delivery_url, $str_to_check)) {
          $webhook->set_status('active');
          $webhook->save();
        }
      }
    }

    public static function addNoteOnCartSubTotalsSection()
    {
      echo '<tr><td colspan="2" class="subtotal-note"><div>' . BBAM_Utils::getConfig('quote_notes') . '</div></td></tr>';
    }
    
    public static function add_goods_check_in_cart_page()
    {
      $is_residential_delivery = BBAMastro::getResidentialValue();
      $is_tailgate_delivery = BBAMastro::getTailgateDeliveryValue();

      echo '<tr><th>Goods Check</th><td>';

      if(BBAM_Utils::getConfig('show_residential_delivery') === 'yes') {
        woocommerce_form_field('residential', [
          'type' => 'checkbox',
          'label' => __('Residential Delivery'),
          'id' => 'bba-residential'
        ], $is_residential_delivery);
        echo '<br />';
      }

      if (BBAM_Utils::getConfig('show_tailgate_delivery') === 'yes') {
        woocommerce_form_field('tailgate', [
          'type' => 'checkbox',
          'label' => __('Tailgate Delivery'),
          'id' => 'bba-tailgate'
        ], $is_tailgate_delivery);
      }

      echo '</td></tr>';
    }

    public static function add_goods_check_in_checkout_page()
    {
      $is_residential_delivery = BBAMastro::getResidentialValue();
      $is_tailgate_delivery = BBAMastro::getTailgateDeliveryValue();

      echo '<tr><th>Goods Check</th><td>';

      if (BBAM_Utils::getConfig('show_residential_delivery') === 'yes') {
        woocommerce_form_field('residential', [
          'type' => 'checkbox',
          'label' => __('Residential Delivery'),
          'id' => 'bba-checkout-residential'
        ], $is_residential_delivery);

        echo '<br />';
      }
      
      if (BBAM_Utils::getConfig('show_tailgate_delivery') === 'yes') {
        woocommerce_form_field('tailgate', [
          'type' => 'checkbox',
          'label' => __('Tailgate Delivery'),
          'id' => 'bba-checkout-tailgate'
        ], $is_tailgate_delivery);

      }

      echo '</td></tr>';
    }

    public static function getResidentialValue()
    {
      if (isset(WC()->session)) {
        if (BBAM_Utils::getConfig('show_goods_check') === 'no') {
          return BBAM_Utils::getConfig('is_residential') === 'yes' ? true : false;
        }

        if (WC()->session->get('residential') === null) {
          return false;
        }
        return WC()->session->get('residential') === 'false' ? false : true;
      }
    }

    public static function getTailgateDeliveryValue()
    {
      if (isset(WC()->session)) {
        if (BBAM_Utils::getConfig('show_goods_check') === 'no') {
          return BBAM_Utils::getConfig('is_tailgate') === 'yes' ? true : false;
        }

        if (WC()->session->get('tailgate') === null) {
          return false;
        }
        return WC()->session->get('tailgate') === 'false' ? false : true;
      }
    }

    public static function addLoadingIndicator()
    {
      echo "
        <div id='bba-loading-overlay'>
          <div id='bba-loading-spinner'></div>
        </div>
      ";
    }

    public static function updateCartWithGoodsCheck()
    {
      $residentialDelivery = isset($_POST['residential']) ? sanitize_text_field($_POST['residential']) : null;
      $tailgateDelivery = isset($_POST['tailgate']) ? sanitize_text_field($_POST['tailgate']) : null;

      WC()->session->set('residential', $residentialDelivery);
      WC()->session->set('tailgate', $tailgateDelivery);

      $bbaShipping = new BBAM_ShippingRules();
      $cartItems = WC()->cart->get_cart();
      $shippingAddress = WC()->customer->get_shipping();

      $bbaShipping->calculate_shipping([
        'contents' => $cartItems,
        'destination' => $shippingAddress
      ]);

      $rates = [];
      foreach ($bbaShipping->rates as $rate) {
        $rates[] = [
          'label' => $rate->get_label(),
          'shipping_id' => $rate->get_id(),
          'cost' => $rate->get_cost()
        ];
      }

      wp_send_json_success($rates);
    }

    public static function addGoodsCheckToOrder($order_id)
    {
      $order = wc_get_order($order_id);
      $order->update_meta_data('residential_delivery', BBAMastro::getResidentialValue());
      $order->update_meta_data('tail_gate_delivery', BBAMastro::getTailgateDeliveryValue());
      $order->save();
    }

    public static function shippingMethods($methods)
    {
      $methods['bbamastro_rules'] = 'BBAM_ShippingRules';
      return $methods;
    }

    public static function reorderCheckoutFields($address_fields)
    {
      $fieldsArray = array(
        'company',
        'first_name',
        'last_name',
        'address_1',
        'address_2',
        'country',
        'state',
        'city',
        'postcode',
        'phone',
        'email'
      );

      for ($i = 0; $i < count($fieldsArray); $i++) {
        $priority = $i * 10;
        $address_fields[$fieldsArray[$i]]['priority'] = $priority;
      }

      return $address_fields;
    }

    private function initializeConstants()
    {
      BBAM_Utils::defineConstantIfNotExists('BBAMASTRO_PLUGIN_FILE', __FILE__);
      BBAM_Utils::defineConstantIfNotExists('BBAMASTRO_PLUGIN_BASENAME', plugin_basename(__FILE__));
      BBAM_Utils::defineConstantIfNotExists('BBAMASTRO_VERSION', $this->version);
    }

    public static function hideFlatRate($rates)
    {
      $show_flat_rate = BBAM_Utils::getConfig('show_flat_rate');
      $bba_mastro_rules_index = BBAM_Utils::getShippingMethodIndex($rates, 'bbamastro_rules');
      $flat_rate_quote_index = BBAM_Utils::getShippingMethodIndex($rates, 'flat_rate');

      if ($show_flat_rate === 'no' && $bba_mastro_rules_index != -1 && $flat_rate_found != -1) {
        $new_rates = array();

        foreach ($rates as $rate_id => $rate) {
          if ($rate->method_id !== 'flat_rate') {
            $new_rates[$rate_id] = $rate;
          }
        }

        return $new_rates;
      }

      return $rates;
    }

    public static function removeGoodsCheckSession($order_id)
    {
      if (function_exists('WC')) {
        WC()->session->destroy_session();
      }
    }
  }
}

function BBAM()
{
  return BBAMastro::getInstance();
}
BBAM();
