<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class BBAM_Shipping_Calculator extends BBAM_Api
{

    public function generateGetQuotesParams($package = [])
    {
        $shippingRules = new BBAM_ShippingRules();

        $params = [];
        $default = $shippingRules->getPackageSettings();

        $params['channel'] = (string)$shippingRules->get_option('channel');
        $params['source'] = $shippingRules->getShippingOrigin();
        $params['destination'] = $shippingRules->getShippingDestination($package['destination']);
        $params['items'] = [];
        $params['pickupDetails'] = [
            'asap' => true,
            'pickupDate' => null,
            'pickupEarliestTime' => null,
            'pickupLatestTime' => null,
            'pickupTimezone' => get_option('gmt_offset')
        ];

        $params['residentialDelivery'] = BBAM_Utils::isResidentialDelivery();
        $params['tailGateDelivery'] = BBAM_Utils::isTailgateDelivery();

        # todo: need to modify this code
        if (!isset($_REQUEST['product-id'])) {
            foreach ($package['contents'] as $item) {
                $terms_list = wp_get_post_terms($item['product_id'], 'product_cat');
                $excluded = $this->is_in_excluded_categories($terms_list);

                if (!$excluded) {
                    $params['items'][] = $this->generateItemData($item, $default);
                }
            }
        } else {
            $productId = absint($_REQUEST['product-id']);
            $terms_list = wp_get_post_terms($productId, 'product_cat');
            $excluded = $this->is_in_excluded_categories($terms_list);

            if (!$excluded) {
                $product = wc_get_product($productId);
                $params['items'][] = $this->generateItemData($product, $default);
            }
        }

        $params['packages'] = [];
        foreach ($package['contents'] as $item) {
            $terms_list = wp_get_post_terms($item['product_id'], 'product_cat');
            $excluded = $this->is_in_excluded_categories($terms_list);
            if ($item['data']->needs_shipping() && !$excluded) {
                $params['packages'][] = $this->generatePackageData($item, $default);
            }
        }
        return $params;
    }

    public function calculatePackaging($package = [])
    {
        $api = new BBAM_Api();
        $shippingRules = new BBAM_ShippingRules();
        $default = $shippingRules->getPackageSettings();
        $payload = $this->generateCalculatePackagingItemsPayload($package['contents'], $default);

        if (count($payload) < 1) return [];
        $response = $api->sendApiRequest('POST', 'packaging/packaging/calculate', [], $payload, true);
        return $this->generatePackagingData($response, $payload, $default);
    }

    private function generateCalculatePackagingItemsPayload($itemsData, $defaultDimensions)
    {
        $items = [];
        foreach ($itemsData as $item) {
            if (isset($item['addons'])) {
                $item_addons = $item['addons'];
                if (is_array($item_addons)) {
                    foreach ($item_addons as $addon) {
                        $addonData = wc_get_product($addon['woo_ldd_product']);
                        $items[] = [
                            'sku' =>  $addonData->get_sku(),
                            'quantity' => $item['quantity'],
                            'length' => (float)  $addonData->get_length() ?: $defaultDimensions['length'],
                            'width' => (float)  $addonData->get_width() ?: $defaultDimensions['width'],
                            'height' => (float)  $addonData->get_height() ?: $defaultDimensions['height'],
                            'weight' => (float)  $addonData->get_weight() ?: $defaultDimensions['weight'],
                            'weightUnit' => 'Kilograms',
                            'measureUnit' => 'CM',
                        ];
                    }
                }
            }

            $terms_list = wp_get_post_terms($item['product_id'], 'product_cat');
            $excluded = $this->is_in_excluded_categories($terms_list);
            if ($item['data']->needs_shipping() && !$excluded) {
                $items[] = [
                    'sku' => $item['data']->get_sku(),
                    'quantity' => $item['quantity'],
                    'length' => (float) $item['data']->get_length() ?: $defaultDimensions['length'],
                    'width' => (float) $item['data']->get_width() ?: $defaultDimensions['width'],
                    'height' => (float) $item['data']->get_height() ?: $defaultDimensions['height'],
                    'weight' => (float) $item['data']->get_weight() ?: $defaultDimensions['weight'],
                    'weightUnit' => 'Kilograms',
                    'measureUnit' => 'CM',
                ];
            }
        }
        return $items;
    }

    private function generatePackagingData($packaging_data, $items_data, $default_dimensions)
    {
        $packaging = [];
        if (empty($packaging_data)) {
            foreach ($items_data as $item) {
                $packaging[] = [
                    'quantity' => (int) $item['quantity'],
                    'length' => (float) $item['length'] ?: $default_dimensions['length'],
                    'width' => (float) $item['width'] ?: $default_dimensions['width'],
                    'height' => (float) $item['height'] ?: $default_dimensions['height'],
                    'weight' => (float) $item['weight'] ?: $default_dimensions['weight'],
                    'weightUnit' => 'Kilograms',
                    'measureUnit' => 'CM',
                    'contentWeight' => (float) $item['weight'] ?: $default_dimensions['weight']
                ];
            }
        } else {
            if (is_array($packaging_data)) {
                $selected_packaging = $packaging_data[0];
                foreach ($selected_packaging->packaging as $pkg) {
                    $packaging[] = [
                        'length'   => (float) $pkg->length,
                        'width'    => (float) $pkg->width,
                        'height'   => (float) $pkg->height,
                        'weight'   => (float) $pkg->maxWeight,
                        'quantity' => (int) $pkg->qty,
                        'weightUnit' => $pkg->weightUnit,
                        'measureUnit' => $pkg->measureUnit,
                        'contentWeight' => (float) $pkg->contentWeight
                    ];
                }
            }
        }
        return $packaging;
    }

    private function generateItemData($item, $default)
    {
        return [
            'quantity' => (int) $item['quantity'],
            'value' => (int) $item['data']->get_regular_price(),
            'length' => (float) $item['data']->get_length() ?: $default['length'],
            'width' => (float) $item['data']->get_width() ?: $default['width'],
            'height' => (float) $item['data']->get_height() ?: $default['height'],
            'weight' => (float) $item['data']->get_weight() ?: $default['weight'],
            'code' => 'TST',
            'weightUnit'        => 'Kilograms',
            'measureUnit'       => 'CM'
        ];
    }

    private function is_in_excluded_categories($terms_list)
    {
        $excluded_cat_str = BBAM_Utils::getConfig('excluded_categories');
        
        if (!is_null($excluded_cat_str)) {
            $excluded_cat_array = explode(',', strtolower($excluded_cat_str));
            foreach ($terms_list as $category) {
                if (in_array(strtolower($category->name), $excluded_cat_array)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function generatePackageData($item, $default)
    {
        return [
            'length'   => (float) $item['data']->get_length() ?: $default['length'],
            'width'    => (float) $item['data']->get_width() ?: $default['width'],
            'height'   => (float) $item['data']->get_height() ?: $default['height'],
            'weight'   => (float) $item['data']->get_weight() ?: $default['weight'],
            'quantity' => (int) $item['quantity'],
            'weightUnit' => 'Kilograms',
            'measureUnit' => 'CM',
            'contentWeight' => (float) $item['data']->get_weight() ?: $default['weight']
        ];
    }

    public function getQuotes($params = [])
    {
        if (BBAM_Utils::getConfig('enabled') == 'yes') {
            $api = new BBAM_Api();
            BBAM_Utils::logger($params, 'QUOTE REQUEST PAYLOAD', 'bba-api-quote-request-payload');

            if (BBAM_Utils::getConfig('use_new_api') == 'yes') {
                $response = $api->sendApiRequest('POST', 'carrier/quote-seq', [], $params, true);
                BBAM_Utils::logger($response, 'QUOTE RAW RESPONSE', 'bba-api-quote-raw-response');
                return $this->parseNewAPIQuotes($response);
            } else {
                $response = $api->sendApiRequest('POST', 'carrier/quote', [], $params, true);
                BBAM_Utils::logger($response, 'NONE QUOTE SEQ RAW RESPONSE', 'bba-api-quote-raw-response');
                return $this->parseOldAPIQuotes($response);
            }
        }
    }

    private function parseNewAPIQuotes($response)
    {
        $rates = [];
        if (is_array($response)) {
            if (!empty($response)) {
                for ($i = 0; $i < count($response); $i++) {
                    $carrier = $response[$i];

                    $displayName = $carrier->name;
                    $carrierProfileId = $carrier->carrierProfileId;
                    $serviceProfileId = $carrier->serviceProfileId;
                    $eta = $carrier->eta;
                    $cost = BBAM_Utils::getConfig('round_up_quote_values') == 'yes' ? $this->roundUp($carrier->amount) : $carrier->amount;
                    $tax = $carrier->tax;
                    $currency = $carrier->currency;
 
                    $isFormattedName = BBAM_Utils::getConfig('format_quote_names');
                    if ($isFormattedName === 'yes') {
                        $label = $carrier->carrierName . ', ' . $this->showOnlyMaxTransitDays($eta);
                    } else {
                        $label = $displayName . ' [' . $eta . ']' . '[Tax: ' . $currency . ' $' . $tax . ' ]';
                    }

                    $rates[] = [
                        'id' => 'bbamastro_rules:'.$carrierProfileId . '_' . $serviceProfileId,
                        'label' => $label,
                        'cost' => $cost,
                        'meta_data' => [
                            'shipping_method' => $carrierProfileId . '_' . $serviceProfileId,
                        ]
                    ];
                }
            }
        }

        return $rates;
    }

    private function parseOldAPIQuotes($response)
    {
        $rates = [];
        BBAM_Utils::logger($response, 'RAW RESPONSE', 'parse-old-api-quotes');
        if (is_array($response)) {
            if (!empty($response)) {
                for ($i = 0; $i < count($response); $i++) {
                    $carrier = $response[$i];
                    $serviceProfileName = $carrier->serviceProfileName;
                    $displayName = $carrier->displayName;
                    $carrierProfileId = $carrier->carrierProfileId;
                    $serviceProfileId = $carrier->serviceProfileId;
                    $eta = $carrier->eta;
                    $cost = BBAM_Utils::getConfig('round_up_quote_values') == 'yes' ? $this->roundUp($carrier->amount) : $carrier->amount;
                    $tax = $carrier->tax;
                    $currency = $carrier->currency;

                    $label = $displayName ? $displayName : $serviceProfileName;
                    $isFormattedName = BBAM_Utils::getConfig('format_quote_names');

                    if ($isFormattedName === 'yes') {
                        $carrierName = $carrier->carrierName;
                        $label = $carrierName ? $carrierName : $label . ', ' . $this->showOnlyMaxTransitDays($eta);
                    } else {
                        $label = $label . ' [' . $eta . ']' . '[Tax: ' . $currency . ' $' . $tax . ' ]';
                    }

                    $rates[] = [
                        'id' => 'bbamastro_rules:'.$carrierProfileId . '_' . $serviceProfileId,
                        'label' => $label,
                        'cost' => $cost,
                        'meta_data' => [
                            'shipping_method' => $carrierProfileId . '_' . $serviceProfileId,
                        ]
                    ];
                }
            }
        }

        return $rates;
    }

    private function roundUp($amount) {
        return ceil($amount);
    }

    private function showOnlyMaxTransitDays($eta) {
        $parsedEta = explode('-', $eta);
        
        if (count($parsedEta) > 1) {
            $day =  preg_replace("/[^0-9]/", "", $parsedEta[1]);
            return $day.' Day(s)';
        }

        $day =  preg_replace("/[^0-9]/", "", $eta);
        return $day. ' Days(s)';
    } 
}
