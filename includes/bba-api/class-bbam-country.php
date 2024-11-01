<?php
class BBAM_Country {
    protected $wc_countries;

    public function __construct() {
        $this->wc_countries = new WC_Countries(); // this class is not found 
    }

    public function getCountries() {
        if(get_option('woocommerce_allowed_countries') == 'specific') {
            return $this->wc_countries->get_allowed_countries();
        } else {
            return $this->wc_countries->get_countries();
        }
    }

    public function getCode(){
        return array_keys($this->getOptions());
    }

    public function getOptions($to_json = false){
        $countries = $this->getCountries();
        if($to_json) {
            $result = [];
            foreach($countries as $id => $text) {
                $obj = new stdClass();
                $obj->id = $id;
                $obj->text = html_entity_decode($text);
                $result[] = $obj;
            }
            return $result;
        }
        return $countries;
    }
}