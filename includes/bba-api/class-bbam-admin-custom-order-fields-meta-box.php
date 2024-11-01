<?php
class BBAM_AdminCustomOrderFieldsMetabox {
    public function __construct(){
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_consignent_and_tracking'), 10, 1);
    }

    public function add_consignent_and_tracking($order){
		$custom_order_fields = get_option('bba_admin_custom_order_fields');
		$order_meta = $order->get_meta_data();

		echo "<h4>BBA MASTRO TRACKING DETAILS</h4>";
		echo "<ul>";

		foreach($custom_order_fields as $field_id => $field) {
			$label = $field['label']; 
			$value = $this->get_meta_data_value($field_id, $order_meta);

			echo "<li>"."<b>".$label."</b>".': '.$value."</li>";
		}

		echo "</ul>";
    }

	private function get_meta_data_value($field_id, $order_meta_data) {
		$meta_key_to_find = '_wc_acof_'.$field_id;

		$value = 'N/A';
		foreach($order_meta_data as $meta) {
			if ($meta->key === $meta_key_to_find) {
				$value = $meta->value;
				break;
			}
		}

		return $value;
	}

	// todo: add implementation code here...
    public function render() {
        global $post;
        $instance = BBAM();
        $order_fields = $instance::$bbamTrackingConsignmentInstance->getOrderFields(( $post->ID ));	

		if (true) : ?>
			<ul>
				<?php foreach ( $order_fields as $field ) : ?>
					<li class="form-field" style="width: 50%; float: left;">
						<label for="bba-admin-custom-order-fields-input-<?php echo esc_attr( $field->id ); ?>">
							<?php esc_html_e( $field->label, 'bba-admin-custom-order-fields' ); ?>
							<?php if ( $field->is_required() ) : ?>
								<span class="required">*</span>
							<?php endif; ?>
							<?php if ( ! empty( $field->description ) ) : ?>
								<?php echo wc_help_tip( $field->description ); ?>
							<?php endif; ?>
						</label>
						<p><?= $field->get_value() ? $field->get_value() : 'N/A' ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<div style="clear: both;"></div>
			<?php endif;
    }
}