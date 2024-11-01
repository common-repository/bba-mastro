<?php defined( 'ABSPATH' ) or exit; ?>

<h2><?php esc_html_e( 'Additional Order Details', 'bba-admin-custom-order-fields' ); ?></h2>
<ul style="list-style: none; padding: 0;">
	<?php foreach ( $order_fields as $order_field ) : ?>

		<?php if ( $order_field->is_visible() && ( $value = $order_field->get_value_formatted() ) ) : ?>

			<li style="padding-bottom: 10px;">
				<strong><?php echo wp_kses_post( $order_field->label ); ?>:</strong>
				<div class="text" style="padding-left: 20px;">
					<?php echo 'textarea' === $order_field->type ? wpautop( wp_kses_post( $value ) ) : wp_kses_post( $value ); ?>
				</div>
			</li>

		<?php endif; ?>

	<?php endforeach; ?>

</ul>
