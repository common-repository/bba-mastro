<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBAM_CustomOrderFields {
    private $data;
    private $id; 
    public $value; 
    private $has_run_field_options_filter = false;

    public function __construct($id, array $data){
        $this->id = $id; 
        $this->data = $data;
    }

    public function __get($key) {
        switch($key) {
            case 'id': 
                return $this->id;
            
            case 'label': 
                return apply_filters( 'bba_admin_custom_order_fields_field_label', $this->data['label'], $this );
            
            case 'type': 
                return $this->data['type'];
            
            case 'description': 
                return  isset( $this->data['description'] ) ? $this->data['description'] : null;
            
            case 'required':
				return $this->is_required();

			case 'visible':
				return $this->is_visible();

			case 'listable':
				return $this->is_listable();

			case 'sortable':
				return $this->is_sortable();

			case 'filterable':
				return $this->is_filterable();

			default:
				return null;
        }
    }

    public function __isset( $key ) {
		switch( $key ) {
			case 'required':
			case 'visible':
			case 'listable':
			case 'sortable':
			case 'filterable':
				return true;

			case 'value':
				return isset( $this->value );

			default:
				return isset( $this->data[ $key ] );
		}
    }
    
    public function get_id() {
		return $this->id;
    }
    
    public function get_type() {
		return $this->data['type'];
    }
    
    public function get_meta_key() {
		return '_wc_acof_' . $this->id;
    }
    
    public function set_value( $value ) {
		$this->value = $value;
		if ( $this->has_options() ) {

			if ( ! is_array( $value ) ) {
				$value = array( $value );
            }
            
			$this->get_options();

			foreach ( $this->data['options'] as $key => $option ) {

				if ( in_array( $option['value'], $value, true ) ) {
					$this->data['options'][ $key ]['selected'] = true;
				} else {
					$this->data['options'][ $key ]['selected'] = false;
				}
			}
		}
    }
    
    public function get_value() {
		$value = $this->value;
		if ( ! isset( $this->value ) && $this->default ) {

			if ( 'date' === $this->type && 'now' === $this->default ) {
				$value = time();
			} else {
				$value = $this->default;
			}
		}

		return $value;
    }
    
    public function get_value_formatted() {

		$value = $this->value;

		// note we use value directly to avoid returning a default that would be displayed to a user
		switch ( $this->type ) {

			case 'date':
				$value_formatted = $value ? date_i18n( wc_date_format(), $value ) : ''; // TODO: NEED TO CHECK THIS ONE
			break;

			case 'select':
			case 'multiselect':
			case 'checkbox':
			case 'radio':

				$options = $this->get_options();

				$value = array();

				foreach ( $options as $option ) {

					if ( $option['selected'] ) {

						$value[] = $option['label'];
					}
				}

				$value_formatted = implode( ', ', $value );

			break;

			default:
				$value_formatted = $value;
			break;

		}
		return apply_filters( 'bba_admin_custom_order_fields_field_value_formatted', $value_formatted, $this );
    }
    
    public function has_options() {
		return in_array( $this->type, array( 'select', 'multiselect', 'radio', 'checkbox' ), true );
    }
    
    public function get_options() {
		if ( ! $this->has_options() ) {
			return null;
		}

		$options = isset( $this->data['options'] ) && $this->data['options'] ? $this->data['options'] : array();
		if ( ! $this->has_run_field_options_filter ) {
			$this->data['options'] = $options = apply_filters( 'bba_admin_custom_order_field_options', $options, $this );
			$this->has_run_field_options_filter = true;
		}

		// set default values if no value provided
		if ( ! isset( $this->value ) ) {

			foreach ( $options as $key => $option ) {

				if ( $option['default'] ) {
					$options[ $key ]['selected'] = true;
				} else {
					$options[ $key ]['selected'] = false;
				}
			}
		}

		// add an empty option for non-required select/multiselect
		if ( ( 'select' === $this->type || 'multiselect' === $this->type ) && ! $this->is_required() ) {
			array_unshift( $options, array( 'default' => false, 'label' => '', 'value' => '', 'selected' => false ) );
		}

		return $options;
    }
    
    public function is_required() {

		return isset( $this->data['required'] ) && $this->data['required'];
    }
    
    public function is_visible() {

		return isset( $this->data['visible'] ) && $this->data['visible'];
	}

    public function is_listable() {

		return isset( $this->data['listable'] ) && $this->data['listable'];
    }
    
    public function is_sortable() {

		return $this->is_listable() && isset( $this->data['sortable'] ) && $this->data['sortable'];
    }
    
    public function is_filterable() {

		return $this->is_listable() && isset( $this->data['filterable'] ) && $this->data['filterable'];
    }
    
    public function is_numeric() {

		return 'date' === $this->type || ( isset( $this->data['is_numeric'] ) && $this->data['is_numeric'] );
	}
}