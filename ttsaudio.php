<?php
/*
Plugin Name: TTS Audio
Plugin URI: http://ttsmovie.com/plugin/ttsaudio
Description: This plugin help you convert your text to speech.
Author: TTS Movie
Version: 1.0
Author: TTS Movie
Author URI: http://ttsmovie.com/
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
	wp_enqueue_script( 'ttsaudio-plyr', 'https://cdn.plyr.io/2.0.18/plyr.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-playlist', ttsaudio_plugin_url . 'assets/js/plyr-playlist.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-html5media', '//api.html5media.info/1.2.2/html5media.min.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-rangetouch', 'https://cdn.rangetouch.com/1.0.1/rangetouch.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-ResizeSensor', ttsaudio_plugin_url . 'assets/js/ResizeSensor.js', false, false, true);
	wp_enqueue_script( 'ttsaudio-ElementQueries', ttsaudio_plugin_url . 'assets/js/ElementQueries.js', false, false, true);

}

add_filter( 'the_content', array($tts, 'ttsAudioContent') );

add_action( 'activated_plugin', 'detect_plugin_activation', 10, 2 );
function detect_plugin_activation( $plugin, $network_activation ) {

	$page_title = 'TTSAudio';
	$id = post_exists($page_title);
	if($id < 1) $id = wp_insert_post(array('post_title'=>$page_title, 'post_type'=>'page', 'post_status'=>'publish'));

	$options = get_option( ttsaudio_option_name );
	if($options['mp3_dir']!=='') $fullpath = $options['mp3_dir'];
	else {
		$upload_dir = wp_upload_dir();
		$fullpath = addslashes($upload_dir['basedir'] . '/'.'TTSAudio_'.rand(0,999).'/');
		if(!file_exists($fullpath)) wp_mkdir_p( $fullpath );
	}
	$data_options =  array('page_id' => $id	,'plyr_skin' => 'default','default_voice' => 'en-US_MichaelVoice','fpt_api_key' => '','mp3_dir' =>$fullpath);
	update_option(ttsaudio_option_name, $data_options);
}

add_action( 'template_include', array($tts, 'template_include') );
add_action( 'wp_footer', array($tts, 'footer_script'), 100 );