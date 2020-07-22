<?php
/*
Plugin Name: TTS Audio
Plugin URI: http://gearthemes.com/ttsaudio
Description: This plugin help you convert your text to speech.
Author: Duc Nguyen (0936 770 119)
Author URI: https://fb.com/ducwp
Version: 1.0
Text Domain: ttsaudio
Domain Path: /languages
*/

define('ttsaudio_option_name', '_ttsaudio_options' );
define('ttsaudio_plugin_url', plugin_dir_url( __FILE__ ));
define('ttsaudio_skins_dir', plugin_dir_path( __FILE__ ) . 'assets/css/skins/' );

require_once('inc/class.TTSAudio.php');
require_once('inc/options.php');
require_once('inc/class.Widgets.php');
require_once('inc/metaboxes.php');

$tts = new TTSAudio;

add_action( 'plugins_loaded', 'ttsaudio_load_textdomain' );
function ttsaudio_load_textdomain() {
  load_plugin_textdomain( 'ttsaudio', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'wp_enqueue_scripts', 'ttsaudio_plugin_scripts' );
function ttsaudio_plugin_scripts(){
	$options = get_option( ttsaudio_option_name );
	wp_enqueue_style( 'ttsaudio-plyr',  'https://cdn.plyr.io/2.0.18/plyr.css' );
	wp_enqueue_style( 'ttsaudio-plyr-playlist',  ttsaudio_plugin_url . 'assets/css/plyr-custom.css' );
	wp_enqueue_style( 'ttsaudio-plyr-skin-'.$options['plyr_skin'],  ttsaudio_plugin_url . 'assets/css/skins/'.$options['plyr_skin'].'.css' );
	$instance = get_option( 'widget_ttsaudio-playlist' );
	if($instance!=='' && is_array($instance)){
		unset($instance['_multiwidget']);
		foreach($instance as $ins){
			if($ins['skin'] !== $options['plyr_skin'])
			wp_enqueue_style( 'ttsaudio-plyr-skin-'.$ins['skin'],  ttsaudio_plugin_url . 'assets/css/skins/'.$ins['skin'].'.css' );
		}
	}

  wp_localize_script( 'jquery', 'ajax_object',
    array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'ajax_nonce' => wp_create_nonce( 'any_value_here' ))
  );
  wp_enqueue_script( 'jquery');
	wp_enqueue_script( 'ttsaudio-plyr', 'https://cdn.plyr.io/2.0.18/plyr.js', 'jquery', false, true);
	wp_enqueue_script( 'ttsaudio-playlist', ttsaudio_plugin_url . 'assets/js/plyr-playlist.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-html5media', '//api.html5media.info/1.2.2/html5media.min.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-rangetouch', 'https://cdn.rangetouch.com/1.0.1/rangetouch.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-ResizeSensor', ttsaudio_plugin_url . 'assets/js/ResizeSensor.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-ElementQueries', ttsaudio_plugin_url . 'assets/js/ElementQueries.js', ['jquery'], false, true);

}

function my_enqueue($hook) {
  if( 'toplevel_page_ttsaudio_options' != $hook ) return;
  wp_enqueue_style( 'ttsaudio-options',  ttsaudio_plugin_url . 'assets/css/options.css' );
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );

add_filter( 'the_content', array($tts, 'ttsAudioContent') );

add_filter( 'query_vars', function( $query_vars ){
    $query_vars[] = 'ttsaudio';
    return $query_vars;
} );

add_action( 'template_include', array($tts, 'template_include') );
add_action( 'wp_footer', array($tts, 'footer_script'), 100 );


function kl_rename_plugin_menus() {
    global $menu;

    // Define your changes here
    $updates = array(
        "ttsaudio_options" => array(
            'name' => 'Testname',
            'icon' => 'dashicons-lock'
        )
    );

    foreach ( $menu as $k => $props ) {

        // Check for new values
        $new_values = ( isset( $updates[ $props[0] ] ) ) ? $updates[ $props[0] ] : false;
        if ( ! $new_values ) continue;

        // Change menu name
        $menu[$k][0] = $new_values['name'];

        // Optionally change menu icon
        if ( isset( $new_values['icon'] ) )
            $menu[$k][6] = $new_values['icon'];
    }
}
add_action( 'admin_init', 'kl_rename_plugin_menus' );
