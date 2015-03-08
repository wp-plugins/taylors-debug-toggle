<?php
/**
*   Plugin Name: Taylor's Debug Toggle
*   Description: Toggle WP_DEBUG on/off through the admin toolbar
*   Author: Taylor Mitchell-St.Joseph
*   Author URI: http://taylormitchellstjoseph.co.uk
*   Version: 1.0
*   Text Domain: tdt
*/
class TaylorsDebugToggle{

    private $wp_config; 

    function __construct() {
        //If not an admin, stop right there!
        if ( ! current_user_can( 'manage_options' ) )
            return;
        
        $this->wp_config = ABSPATH . "wp-config.php";

        add_action( 'init', array( $this, 'tdt_toggle' ), 99 );
        add_action( 'admin_bar_menu', array( $this, "tdt_toggle_links" ), 99 );
    }

    function tdt_toggle_links( $wp_admin_bar ){
        $ignore = array( "tdt_toggle", "tdt_refresh" );
        $current_url = "?";
        if( isset( $_GET ) ){
            foreach( $_GET as $key => $value ){
                if( !in_array( $key, $ignore ) ){
                    $current_url .= "$key=$value&amp;";
                }
            }
        }

        $wp_admin_bar->add_node( array(
            'id'    => 'tdt',
            'title' => 'Debug Toggle',
            'href'  => "#",
            'meta'  => array( 'class' => 'tdt' )
        ) );

        $wp_admin_bar->add_menu( array(
            'parent'    => 'tdt',
            'id'        => "tdt-true",
            'title'     => "True",
            'href'      => $current_url . "tdt_toggle=true"
        ) );

        $wp_admin_bar->add_menu( array(
            'parent'    => 'tdt',
            'id'        => "tdt-false",
            'title'     => "False",
            'href'      => $current_url . "tdt_toggle=false"
        ) );
    }

    function tdt_toggle(){
        if( isset( $_GET['tdt_toggle'] ) && !isset( $_GET['tdt_refresh'] ) ){
            $debug_mode = in_array(array("true", "false"), $_GET['tdt_toggle']) ? $_GET['tdt_toggle'] : 'false'; //If tdt_toggle is not true or false, default it to false to avoid any nasties
            $current_mode = $_GET['tdt_toggle'] == "true" ? "false" : "true";

            $data = file_get_contents( $this->wp_config );
            $data = str_replace( "define('WP_DEBUG', $current_mode);", "define('WP_DEBUG', $debug_mode);", $data );

            if( file_put_contents( $this->wp_config, $data ) ){
                wp_redirect( $_SERVER['REQUEST_URI'] . "&tdt_refresh=true" );
                die();
            }else{
                wp_die( 'Error: Could not wirte to wp-config.php, please check file permissions!' );
            }
        }
    }

}

add_action( 'init', 'tdt_init' ); 
function tdt_init() { 
    global $tdt;
    $tdt = new TaylorsDebugToggle(); 
}