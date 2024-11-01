<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBAM_Token {
    public static function save_token($token) {
        update_option('bba_token', $token);
    }

    public static function get_token() {
        return get_option('bba_token');
    }

    public static function update_token_created_time($time_in_seconds) {
        update_option('bba_token_expiration', $time_in_seconds);
    }

    public static function get_token_created_time() {
        return get_option('bba_token_expiration');
    }
}