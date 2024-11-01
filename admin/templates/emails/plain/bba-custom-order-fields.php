<?php
defined( 'ABSPATH' ) or exit;

/**
 * Renders visible custom order fields below the order details table in emails
 *
 * @type \WC_Custom_Order_Field[] $order_fields Array of order fields.
 *
 * @version 1.8.0
 * @since 1.5.0
 */

echo esc_html__( 'Additional Order Details', 'woocommerce-admin-custom-order-fields' ) . "\n\n";

foreach ( $order_fields as $order_field ) {

	if ( $order_field->is_visible() && ( $value = $order_field->get_value_formatted() ) ) {
		echo wp_kses_post( $order_field->label ) . ": \n";
		echo wp_kses_post( $value ) . "\n\n";
	}
}

echo "\n****************************************************\n\n";
