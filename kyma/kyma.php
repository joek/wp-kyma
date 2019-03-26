<?php
/**
 * Plugin Name: Kyma Connector
 * Plugin URI:  https://github.com/kyma-incubator/wordpress-connector
 * Description: Kyma Eventing and API Integration Plugin.
 * Version:     0.0.1
 * Author:      kyma-project.io
 * Author URI:  https://kyma-project.io/
 * License:     Apache-2.0
 */


if ( !class_exists( 'KymaConnector_Plugin' ) ) {

    require_once( dirname( __FILE__ ) . '/lib/event-settings.php' );


    // TODO: Work on subscriptions out of configuration
    class KymaConnector_Plugin
    {
        const KYMA_USER_NAME= 'kyma';
        const KYMA_USER_EMAIL='admin@kyma.cx';

        public static function install(){
            // TODO: Validate if Basic Auth is enabled
            // TODO: Add Error handling during activation

            add_option('kymaconnector_application_id', '');
            add_option('kymaconnector_name', 'Wordpress');

            // TODO: Update due to registration data
            add_option('kymaconnector_event_url', 'https://gateway.wordpress.cluster.int.faros.kyma.cx/wp-joek/v1/events');
            add_option('kymaconnector_metadata_url', 'https://gateway.wordpress.cluster.int.faros.kyma.cx/wp-joek/v1/metadata/services');

            $user_name = self::KYMA_USER_NAME;
            $user_email = self::KYMA_USER_EMAIL;

            if ( !username_exists( $user_name ) and email_exists($user_email) == false ) {
                $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                if ( !is_wp_error(wp_create_user( $user_name, $random_password, $user_email ))){
                    update_option('kymaconnector_user', $user_name);
                    update_option('kymaconnector_password', $random_password);
                }
            } else {
                
            }

            KymaConnector_EventSettings::install('kymaconnector');
        }
        
        // TODO: Add this to a library
        public static function add_clientcert_header( $ch ) {
            $keyFile = "/admin/mkt-cloud.key";
            $certFile = "/admin/mkt-cloud.crt";
    
            curl_setopt($ch, CURLOPT_SSLKEY, dirname( __FILE__ ) . $keyFile);
            curl_setopt($ch, CURLOPT_SSLCERT, dirname( __FILE__ ) . $certFile);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            
            return $ch;
        }


    }

    register_activation_hook( __FILE__, array( 'KymaConnector_Plugin', 'install' ) );
    
}


if ( is_admin() ) {
    // we are in admin mode
    require_once( dirname( __FILE__ ) . '/admin/kyma-admin.php' );
}
