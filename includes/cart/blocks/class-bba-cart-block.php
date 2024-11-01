<?php 

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

class BbaCartBlockIntegration implements IntegrationInterface {

    public function get_name() {
        return 'bbamastro';
    }

    public function initialize() {
        $script_url = $this->get_base_urls() . '/public/js/bba-cart-block.js';
        $style_url = $this->get_base_urls() . '/pubclic/css/bba-cart-block.css';

        wp_enqueue_style(
			'wc-blocks-integration',
			$style_url,
			[], // todo: need to make this working here
			'1.0'
		);

        wp_register_script(
			'wc-blocks-integration',
			$script_url,
			[], // todo: need to make this working here
			'1.0',
			true
		);
    }

    private function get_base_urls() {
        return untrailingslashit(plugins_url('/')).'/bba-mastro';
    }

    public function get_script_handles() {
        return ['wc-blocks-integration'];
    }

    public function get_editor_script_handles() {
        return ['wc-blocks-integration'];
    }

    public function get_script_data() {
        // todo: need to make this one dynamic
        return [
            'tail_gate_delivery' => true, 
            'residential_delivery' => true
        ];
    }

    public function get_file_version($file) {
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG && file_exists($file)) {
            return filemtime($file);
        }

        return '1.0';
    }
}