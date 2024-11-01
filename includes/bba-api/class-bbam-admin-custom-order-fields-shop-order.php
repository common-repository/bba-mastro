<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBA_AdminCustomOrderFieldsShopOrder {
    public function __construct(){
        add_action('admin_init', array($this, 'loadMetaBox'));
        add_filter('manage_edit-shop_order_columns', array($this, 'renderColumnTitles'), 15);
        add_action('manage_shop_order_posts_custom_column', array($this, 'renderColumnContent'), 5);
    }

    #---- ORDER DETAILS META BOX ----#
    public function loadMetaBox() {
        new BBAM_AdminCustomOrderFieldsMetabox(); // load the meta box
    }

    #---- LISTABLE COLUMNS ----#
    public function renderColumnTitles($columns) {
        $newColumns = array();
        foreach($columns as $name => $value) {
            if($name === 'order_actions') {
                prev($columns);
                break;
            }

            $newColumns[$name] = $value;
        }

        $instance = BBAM();
        foreach($instance::$bbamTrackingConsignmentInstance->getOrderFields() as $orderField) {
            if($orderField->is_listable()){
                $newColumns[$orderField->get_meta_key()] = $orderField->label;
            }
        }

        foreach($columns as $name => $value) {
            $newColumns[$name] = $value;
        }

        return $newColumns;
    }

    public function renderColumnContent($column){
        global $post;
        $instance = BBAM();
        foreach($instance::$bbamTrackingConsignmentInstance->getOrderFields($post->ID) as $orderField) {
            if($column === $orderField->get_meta_key()) {
                echo $orderField->get_value_formatted();
                break;
            }
        }
    }
}