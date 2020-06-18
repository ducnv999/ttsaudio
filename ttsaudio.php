<?php
/*
Plugin Name: TTS Audio
Plugin URI: http://songduc.com/plugin/ttsaudio
Description: This plugin help you convert your text to speech.
Author: Nguyen Van Duc (0936-770-119)
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
	wp_enqueue_style( 'ttsaudio-plyr',  '//cdn.plyr.io/2.0.18/plyr.css' );
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
	wp_enqueue_script( 'ttsaudio-plyr', '//cdn.plyr.io/2.0.18/plyr.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-playlist', ttsaudio_plugin_url . 'assets/js/plyr-playlist.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-html5media', '//api.html5media.info/1.2.2/html5media.min.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-rangetouch', '//cdn.rangetouch.com/1.0.1/rangetouch.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-ResizeSensor', ttsaudio_plugin_url . 'assets/js/ResizeSensor.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-ElementQueries', ttsaudio_plugin_url . 'assets/js/ElementQueries.js', false, false, true);

}

add_filter( 'the_content', array($tts, 'ttsAudioContent') );
add_action( 'wp_footer', array($tts, 'footer_script'), 100 );
