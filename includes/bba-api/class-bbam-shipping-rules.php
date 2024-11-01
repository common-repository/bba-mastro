<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once "class-bbam-shipping-calculator.php";

class BBAM_ShippingRules extends WC_Shipping_Method
{   
    public function __construct()
    {
        $this->id = 'bbamastro_rules';
        $this->title = __('BBA Mastro', 'bbamastro');
        $this->method_title = __('BBA Mastro', 'bbamastro');
        $this->method_description = __('Multi Carrier Shipping and Logistics Technology able to seamlessly integrate into your Woo cart.', 'bbamastro');

        $this->init();
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }
    
    // todo: need to watch this code
    public function process_admin_options() 
    {
        parent::process_admin_options();
        BBAM_Token::save_token(false);
        BBAM_Token::update_token_created_time(0);
    }

    private function init()
    {
        $this->initFormFields();
        $this->init_settings();
    }

    private function initFormFields()
    {
        $this->form_fields = include('bbamastro-config.php');
    }

    public function getShippingOrigin()
    {
        return [
            'country' => $this->get_option('warehouse_country'),
            'city' => $this->get_option('wh_city'),
            'postcode' => $this->get_option('wh_postcode')
        ];
    }

    public function calculate_shipping($package = [])
    {
        try {
            $shippingCalcualtor = new BBAM_Shipping_Calculator();
            $params = $shippingCalcualtor->generateGetQuotesParams($package);
            $packaging = $shippingCalcualtor->calculatePackaging($package);

            $params['packages'] = $packaging;
            $quotes = $shippingCalcualtor->getQuotes($params);

            if (BBAM_Utils::getConfig('sort_quotes') == 'yes') {
                usort($quotes, function ($a, $b) {
                    if ($a['cost'] === $b['cost']) return 0;
                    return ($a['cost'] < $b['cost']) ? -1 : 1;
                });
            }

            if ($quotes) {
                foreach ($quotes as $rate_data) {
                    $this->add_rate($rate_data);
                }
            }

            BBAM_Utils::logger($params, 'QUOTE REQUEST PAYLOAD', 'bba-mastro-quotes-request-payload');
            BBAM_Utils::logger($quotes, 'SHIPPING QUOTES', 'bba-mastro-quotes-response');
        } catch (Exception $e) {
            BBAM_Utils::logger($e->getMessage(), 'CALCULATE SHIPPING ERROR', 'bba-mastro-error');
        }
    }

    public function getShippingDestination($address)
    {
        return [
            'country' => $address['country'],
            'city' => $address['city'],
            'postcode' => $address['postcode']
        ];
    }

    public function getPackageSettings()
    {
        return [
            'length' => (float) $this->get_option('default_length'),
            'width'  => (float) $this->get_option('default_width'),
            'height' => (float) $this->get_option('default_height'),
            'weight' => (float) $this->get_option('default_weight'),
        ];
    }
}
