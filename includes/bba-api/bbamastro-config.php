<?php
     $settings['quote_display'] = array(
        'title' => __('Quote Display Settings'),
        'type'  => 'title'
    );
    
    $settings['enabled'] = array(
        'title'   => __( 'Enable', 'bbamastro' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable BBA Mastro shipping methods', 'bbamastro' ),
        'default' => 'no',
    );

    $settings['format_quote_names'] = array(
        'title'   => __('Format Quote Names'),
        'type'    => 'checkbox',
        'label'   => __('Show formatted quote (Carrier, ETA, and Price only)'),
        'default' => 'yes' 
    );

    $settings['show_flat_rate'] = array(
        'title'   => __('Show Flat Rate'),
        'type'    => 'checkbox',
        'label'   => __('Show flat rate together with bba quotes'),
        'default' => 'yes'
    );

    $settings['round_up_quote_values'] = array(
        'title'   => __('Round Up Prices'),
        'type'    => 'checkbox',
        'label'   => __('Round up bba dynamic quotes pricing'),
        'default' => 'no'
    );

    $settings['quote_notes'] = array(
        'title'       => __( 'Cart and Checkout Note', 'bbamastro' ),
        'type'        => 'text',
        'default'     => '',
        'description' => __( 'Note that will be shown on the checkout and cart subtotal section', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['debugging_settings'] = array(
        'title' => __('Debugging Settings'),
        'type'  => 'title'
    );

    $settings['enable_debug_log'] = array(
        'title'   => __('Logs'),
        'type'    => 'checkbox',
        'label'   => __('Enable debugger logs.'),
        'default' => 'no'
    );

    $settings['api_settings'] = array(
        'title' => __('BBA API Settings'),
        'type'  => 'title'
    );

    $settings['use_new_api'] = array(
        'title'   => __('New Quote API'),
        'type'    => 'checkbox',
        'label'   => __('Use new API to show only cheapest/fastest quotes'),
        'default' => 'no'
    );

    $settings['sort_quotes'] = array(
        'title'   => __('Sort Quotes'),
        'type'    => 'checkbox',
        'label'   => __('Sort quotes from lowest to highest price'),
        'default' => 'no'
    );



    $settings['goods_check_settings'] = array(
        'title' => __('Goods Check Settings'),
        'type'  => 'title'
    );

    $settings['show_residential_delivery'] = [
        'title' => __('Show Residential Delivery'),
        'type' => 'checkbox',
        'label' => __('Show residential delivery checkbox in the cart and checkout page')
    ];
    $settings['is_residential'] = array(
        'title' => __('Residential Delivery'),
        'type'  => 'select',
        'options' => [
            'yes' => 'Yes',
            'no' => 'No'
        ],
        'label' => __('Residential Delivery'),
        'description' => __('Make residential delivery the default setting'),
        'desc_tip'    => true,
        'default'    => 'no'
    );

    $settings['show_tailgate_delivery'] = [
        'title' => __('Show Tailgate Delivery'),
        'type' => 'checkbox',
        'label' => __('Show tailgate delivery checkbox in the cart and checkout page')
    ];
    $settings['is_tailgate'] = array(
        'title' => __('Tailgate Delivery'),
        'type'  => 'select',
        'options' => [
            'yes' => 'Yes',
            'no'  => 'No' 
        ],
        'label' => __('Tailgate Delivery'),
        'description' => __('Make taigate delivery the default setting'),
        'desc_tip'    => true,
        'default'    => 'No'
    );

    $settings['api_credentials'] = array(
        'title' => __( 'API Credentials', 'bbamastro' ),
        'type'  => 'title',
    );

    $settings['api_url'] = array(
        'title'       => __( 'API URL', 'bbamastro' ),
        'type'        => 'hidden',
        'default'     => '',
        'description' => __( 'BBA Mastro API URL.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['auth_url'] = array(
        'title'       => __( 'Auth API URL', 'bbamastro' ),
        'type'        => 'text',
        'default'     => '',
        'description' => __( 'BBA Mastro Auth API URL.', 'bbamastro' ),
        'desc_tip'    => true
    );

    $settings['acc_username'] = array(
        'title'       => __( 'Username', 'bbamastro' ),
        'type'        => 'text',
        'default'     => '',
        'description' => __( 'BBA Mastro account username.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['acc_password'] = array(
        'title'       => __( 'Password', 'bbamastro' ),
        'type'        => 'password',
        'default'     => '',
        'description' => __( 'BBA Mastro account password.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    // edited here
    $settings['reseller_id'] = array(
        'title'       => __( 'Reseller Id', 'bbamastro' ),
        'placeholder' => __( 'Reseller Id', 'bbamastro' ),
        'type'        => 'text',
        'css'         => 'min-width:350px;',
        'default'     => '',
    );
    // end of edit

    $settings['channel'] = array(
        'title'       => __( 'Channel', 'bbamastro' ),
        'type'        => 'text',
        'default'     => '',
        'description' => __( 'Channel that would be use for getting quotes', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['package_settings'] = array(
        'title' => __( 'Default Package Settings', 'bbamastro' ),
        'type'  => 'title',
    );

    $settings['default_length'] = array(
        'title'       => __( 'Length (cm)', 'bbamastro' ),
        'type'        => 'decimal',
        'default'     => '',
        'description' => __( 'Default package length in cm.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['default_width'] = array(
        'title'       => __( 'Width (cm)', 'bbamastro' ),
        'type'        => 'decimal',
        'default'     => '',
        'description' => __( 'Default package width in cm.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['default_height'] = array(
        'title'       => __( 'Height (cm)', 'bbamastro' ),
        'type'        => 'decimal',
        'default'     => '',
        'description' => __( 'Default package height in cm.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['default_weight'] = array(
        'title'       => __( 'Weight (kg)', 'bbamastro' ),
        'type'        => 'decimal',
        'default'     => '',
        'description' => __( 'Default package weight in kg.', 'bbamastro' ),
        'desc_tip'    => true,
    );

    $settings['excluded_categories'] = array(
        'type'        => 'hidden',
    );

    $settings['warehouse_details'] = array(
        'title' => __( 'Warehouse Details', 'bbamastro' ),
        'type'  => 'title',
    );

    $settings['warehouse_country'] = array(
        'title'       => __( 'Country', 'bbamastro' ),
        'placeholder' => 'Choose a country',
        'type'        => 'hidden',
        'css'         => 'min-width:350px;',
        'default'     => '',
    );

    $settings['wh_postcode'] = array(
        'title'       => __( 'Postcode', 'bbamastro' ),
        'placeholder' => __( 'Choose a postcode', 'bbamastro' ),
        'type'        => 'text',
        'css'         => 'min-width:350px;',
        'default'     => '',
    );

    $settings['wh_city'] = array(
        'title'       => __( 'City', 'bbamastro' ),
        'placeholder' => __( 'Choose a city', 'bbamastro' ),
        'type'        => 'text',
        'css'         => 'min-width:350px;',
        'default'     => '',
    );

    return $settings;
