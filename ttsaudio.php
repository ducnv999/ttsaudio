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

define('TTSAUDIO_OPTION', '_ttsaudio_options' );
define('TTSAUDIO_URI', plugin_dir_url( __FILE__ ));
define('TTSAUDIO_DIR', plugin_dir_path( __FILE__ ));
define('TTSAUDIO_SKIN_DIR', plugin_dir_path( __FILE__ ) . 'assets/css/skins/' );

require_once( TTSAUDIO_DIR . 'inc/class.TTSAudio.php');
require_once( TTSAUDIO_DIR . 'inc/options.php');
require_once( TTSAUDIO_DIR . 'inc/class.Widgets.php');
require_once( TTSAUDIO_DIR . 'inc/metaboxes.php');
require_once( TTSAUDIO_DIR . 'scssphp/scss.inc.php');

use ScssPhp\ScssPhp\Compiler;

$scss = new Compiler();

echo $scss->compile('
  $color: #abc;
  div { color: lighten($color, 20%); }
');

$tts = new TTSAudio;

add_action( 'plugins_loaded', 'ttsaudio_load_textdomain' );
function ttsaudio_load_textdomain() {
  load_plugin_textdomain( 'ttsaudio', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'wp_enqueue_scripts', 'ttsaudio_plugin_scripts' );
function ttsaudio_plugin_scripts(){
	$options = get_option( TTSAUDIO_OPTION );
	wp_enqueue_style( 'ttsaudio-plyr',  TTSAUDIO_URI . 'assets/css/plyr.css' );
  wp_enqueue_style( 'ttsaudio-style',  TTSAUDIO_URI . 'assets/css/style.css' );

	wp_enqueue_style( 'ttsaudio-plyr-playlist',  TTSAUDIO_URI . 'assets/css/style.css' );
	// wp_enqueue_style( 'ttsaudio-plyr-skin-'.$options['plyr_skin'],  TTSAUDIO_URI . 'assets/css/skins/'.$options['plyr_skin'].'.css' );
	// $instance = get_option( 'widget_ttsaudio-playlist' );
	// if($instance!=='' && is_array($instance)){
	// 	unset($instance['_multiwidget']);
	// 	foreach($instance as $ins){
	// 		if($ins['skin'] !== $options['plyr_skin'])
	// 		wp_enqueue_style( 'ttsaudio-plyr-skin-'.$ins['skin'],  TTSAUDIO_URI . 'assets/css/skins/'.$ins['skin'].'.css' );
	// 	}
	// }

  wp_localize_script( 'jquery', 'ajax_object',
    array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'ajax_nonce' => wp_create_nonce( 'ttsaudio_nonce' ))
  );
  wp_enqueue_script( 'jquery');
	wp_enqueue_script( 'ttsaudio-plyr', TTSAUDIO_URI . 'assets/js/plyr.js', 'jquery', false, true);
	wp_enqueue_script( 'ttsaudio-playlist', TTSAUDIO_URI . 'assets/js/plyr-playlist.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-html5media', '//api.html5media.info/1.2.2/html5media.min.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-rangetouch', 'https://cdn.rangetouch.com/1.0.1/rangetouch.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-ResizeSensor', TTSAUDIO_URI . 'assets/js/ResizeSensor.js', ['jquery'], false, true);
	wp_enqueue_script( 'ttsaudio-ElementQueries', TTSAUDIO_URI . 'assets/js/ElementQueries.js', ['jquery'], false, true);
}

function my_enqueue($hook) {
  if( 'toplevel_page_ttsaudio_options' != $hook ) return;
  wp_enqueue_style( 'ttsaudio-options',  TTSAUDIO_URI . 'assets/css/options.css' );
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );

add_filter( 'the_content', array($tts, 'ttsAudioContent') );

add_filter( 'query_vars', function( $query_vars ){
    $query_vars[] = 'ttsaudio';
    return $query_vars;
} );

add_action( 'template_include', array($tts, 'template_include') );
add_action( 'wp_enqueue_scripts', array($tts, 'single_script' ) );

add_filter('ttsaudio_skins', 'add_new_skins');
function add_new_skins( $skins ){

  // $skin_arr = ['duc'];
  // array_push($skin_arr, $skins);
  //
  // $output_skins = [];
  // foreach ($skins as $skin) {
  //   $output_skins[$skin] = ucwords(str_replace('-',' - ',$skin ));
  // }

  return $skins;
}
