<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBAM_TrackingIdAndConsignmentIdFields {
    private $pageId; // page suffix
    private $pluginPath;
    
    #---- CONSTRUCTOR ----#
    public function __construct($pluginPath) {
        $this->initialize();
        $this->pluginPath = $pluginPath;
    }

    #---- INITIALIZE METHODS ----#
    private function initialize() {
        new BBA_AdminCustomOrderFieldsShopOrder();
        add_option('bba_admin_custom_order_field_next_field_id', 1);
        add_option( 'bba_admin_custom_order_fields' );
        add_action('admin_menu', array($this, 'addMenuLink'));

        add_action( 'woocommerce_email_after_order_table', array( $this, 'addOrderDetailsAfterOrderTableEmails' ), 20, 3 );
        add_action('wp_insert_post', array($this, 'saveDefaultFieldValues'), 10, 2);

        $this->generateFields();
    }

    #---- GENERATE FIELDS ----#
    private function generateFields(){
        if(!get_option('bba_admin_custom_order_fields')) {
            $fields = [
                $this->generateNextFieldId() => [
                    "label"       => "Tracking Number",
                    "description" => "BBA Mastro Tracking Number",
                    "type"        => "text",
                    "visible"     => true,
                    "listable"    => true,
                    "sortable"    => true,
                    "filterable"  => true
                ],
                $this->generateNextFieldId() => [
                    "label"       => "Consignment Number",
                    "description" => "BBA Mastro Consignment Number",
                    "type"        => "text",
                    "visible"     => true,
                    "listable"    => false,
                    "sortable"    => false,
                    "filterable"  => false
                ]
            ];
            update_option('bba_admin_custom_order_fields', $fields);
        }
    }
    
    private function generateNextFieldId(){
        $next_field_id = get_option('bba_admin_custom_order_field_next_field_id');
        update_option('bba_admin_custom_order_field_next_field_id', ++$next_field_id);
        return $next_field_id;
    }

    #---- ADD MENU LINK ----#
    public function addMenuLink(){
        $this->pageId = add_submenu_page(
            'woocommerce',
            __('BBA Tracking And Consignment Fields', 'bba-mastro-admin-custom-order-fields'),
            __('BBA Tracking And Consignment Fields', 'bba-mastro-admin-custom-order-fields'),
            'manage_woocommerce',
            'bba_admin_custom_order_fields',
            array($this, 'renderPage')
        );
    }

    public function renderPage(){
        include $this->pluginPath.'/admin/templates/wc-settings/bba_admin_custom_order_fields.php';
    }

    #---- SAVE DEFAULT FIELDS ----#
    public function saveDefaultFieldValues($post_id, $post){
        if('shop_order' === $post->post_type) {
            foreach($this->getOrderFields($post_id) as $orderField) {
                if($orderField->default) {
                    // force unique, because oddly this can be invoked when changing the status of an existing order
                    add_post_meta( $post_id, $orderField->get_meta_key(), $orderField->default, true );
                }
            }
        }
    }

    public function getOrderFields($orderId = null, $returnAll = true){
        $orderFields = array();
        $order = $orderId ? wc_get_order($orderId) : null;
        $customOrderFields = get_option('bba_admin_custom_order_fields');

        if(!is_array($customOrderFields)) {
            $customOrderFields = array();
        }

       

        foreach($customOrderFields as $fieldId => $field) {
            $orderField = new BBAM_CustomOrderFields($fieldId, $field);
            $hasValue = false;

            if($order instanceof WC_Order) {
                $set_value = false;
                $value = '';

                if(metadata_exists('post', $orderId, $orderField->get_meta_key())) {
                    $set_value = true;
                    $value = $order->get_meta($orderField->get_meta_key(), true, 'edit');
                }

                if($set_value) {
                    $orderField->set_value($value);
                    $hasValue = true;
                }
            }

            if($returnAll || $hasValue) {
                $orderFields[$fieldId] = $orderField;
            }
        }

        return $orderFields;
    }

    #---- ADD ADDITIONAL FIELDS IN EMAIL ----#
    public function addOrderDetailsAfterOrderTableEmails($order, $_, $plain_text){
        $orderFields = $this->getOrderFields($this->getProp($order, 'id'), true);
        if(empty($orderFields)) {
            return;
        }

        $template = $plain_text ? 'emails/plain/bba-custom-order-fields.php' : 'emails/bba-custom-order-fields.php';
		wc_get_template(
			$template,
			array(
				'order_fields' => $orderFields,
			),
			'',
			$this->pluginPath.'/admin/templates/'
		);
    }

    private function getProp($object, $prop, $context = 'edit'){
        if('shipping_total' === $prop && 'view' !== $context) {
            $prop = 'order_shipping';
        }
        elseif('parent_id' === $prop) {
            return $object->post->post_parent;
        }
        
        $value = '';
        if(is_callable(array($object, "get_{$prop}"))) {
            $value = $object->{"get_{$prop}"}($context);
        }
        return $value;
    }
}

