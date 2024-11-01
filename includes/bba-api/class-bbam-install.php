<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BBAM_Install {
    public static function init(){
        add_filter( 'plugin_action_links_' . BBAMASTRO_PLUGIN_BASENAME, array( __CLASS__, 'pluginActionLinks' ) );
        add_filter( 'plugin_row_meta', array( __CLASS__, 'pluginRowMeta' ), 10, 2 );
    }

    public static function pluginActionLinks($links) {
        if(BBAM_Utils::woocommerceEnabled()) {
            $actionsLinks = [
                'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=bbamastro_rules' ) . '" title="' . esc_attr( __( 'View Settings', 'bbamastro' ) ) . '">' . __( 'Settings', 'bbamastro' ) . '</a>',
            ];
            return array_merge($actionsLinks, $links);
        }
        return $links;
    }

    public static function pluginRowMeta($links, $file) {
        if($file == BBAMASTRO_PLUGIN_BASENAME) {
            $rowMeta = [
                'Quickstart Guide'    => '<a href="' . esc_url( apply_filters( 'bbamastro_docs_url', 'https://bbamastro.atlassian.net/wiki/download/attachments/363561064/BBA_Woocommerce_wordpress_QuickStartGuide_final.pdf?version=1&modificationDate=1522813629916&cacheVersion=1&api=v2&download=true' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'bbamastro' ) ) . '">' . __( 'Quickstart Guide', 'bbamastro' ) . '</a>'
            ];
            return array_merge($links, $rowMeta);
        }
        return (array) $links;
    }
}

BBAM_Install::init();