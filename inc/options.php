<?php
add_action('wp_ajax_ttsaudio_plugin_data_save', 'ttsaudio_plugin_save_ajax');
function ttsaudio_plugin_save_ajax() {

	check_ajax_referer('ttsaudio_plugin_options', 'secure');

	$data = $_POST;
	unset($data['security'], $data['action']);

	if(!is_array(get_option(TTSAUDIO_OPTION))) $options = array();
	else $options = get_option(TTSAUDIO_OPTION);

	if($options['mp3_dir']!='' && file_exists($options['mp3_dir'])) {
		rename($options['mp3_dir'], $data['mp3_dir']);
	} else {
		if(!file_exists($data['mp3_dir'])) wp_mkdir_p( $data['mp3_dir'] );
	}

	if(!empty($data)) {
		$diff = array_diff($options, $data);
		$diff2 = array_diff($data, $options);
		$diff = array_merge($diff, $diff2);
	} else $diff = array();

	if(!empty($diff)) {
		if(update_option(TTSAUDIO_OPTION, $data)) die('1');
		else die('0');
	} else die('1');

}

add_action( 'admin_menu', 'theme_options_add_page' );
function theme_options_add_page() {
	add_menu_page( __( 'TTS Audio Options', 'ttsaudio' ), __( 'TTS Audio', 'ttsaudio' ), 'edit_theme_options', 'ttsaudio_options', 'theme_options_do_page', 'dashicons-controls-volumeon' );
}

/**
 * Create the options page
 */
function theme_options_do_page() {
	$options = get_option( TTSAUDIO_OPTION );
	$tts = new TTSAudio;
?>
	<div class="wrap ttsaudio-options">
		<?php printf('<h2>%1$s <a class="buy-pro dashicons-before dashicons-cart" href="%2$s" title="%3$s" >%3$s</a></h2>', __( 'TTS Audio Options', 'ttsaudio' ), 'https://gearthemes.com/ttsaudio-pro', 'buy TTSAudio Pro');?>
		<div id="saved"></div>
		<form action="/" name="ttsaudio_form" id="ttsaudio_form">

			<table class="form-table">

				<tr valign="top"><th scope="row"><?php _e( 'Player Skin', 'ttsaudio' ); ?></th>
					<td>
						<select name="plyr_skin" class="regular-text">
							<?php
							$skins = $tts->PlyrSkin(TTSAUDIO_SKIN_DIR);
							foreach ( $skins as $key => $value ) {
								?>
									<option value="<?php echo esc_attr( $key );?>" <?php selected( $options['plyr_skin'], esc_attr( $key ) ); ?>><?php echo esc_attr( $value );?></option>
							<?php
							}
							?>
						</select>
						<p class="description"><?php _e( 'This skin will be appeared in Single post and default for TTSaudio widget.', 'ttsaudio' ); ?></p>
					</td>
				</tr>

				<tr valign="top"><th scope="row"><?php _e( 'Default Voice', 'ttsaudio' ); ?></th>
					<td>
						<select name="default_voice" class="regular-text">
							<?php
							foreach ( $tts->voices as $key => $value ) {
								?>
									<option value="<?php echo esc_attr( $key );?>" <?php selected( $options['default_voice'], esc_attr( $key ) ); ?>><?php echo esc_attr( $value );?></option>
							<?php
							}
							?>
						</select>
						<p class="description"><?php _e( 'You can change this option during add/edit post.', 'ttsaudio' ); ?></p>
					</td>
				</tr>

				<tr valign="top"><th scope="row"><?php _e( 'FPT API Key', 'ttsaudio' ); ?></th>
					<td>
						<input class="regular-text" type="text" name="fpt_api_key" value="<?php esc_attr_e( $options['fpt_api_key'] ); ?>" />
						<p class="description"><?php _e('If you don\'t use Vietnamese, leave it blank.');?> <a href="https://console.fpt.ai/" target="_blank"><small>Get FPT API KEY</small></a> (max 5000 characters)</p>
					</td>
				</tr>

				<tr valign="top"><th scope="row"></th>
					<td>
						<div class="widget-control-actions">
							<div class="alignleft">
							<input type="submit" class="of-btn-save button-primary" value="<?php _e( 'Save Options', 'ttsaudio' ); ?>" />
							<span class="spinner"></span>
							</div>
							<br class="clear">
						</div>
					</td>
				</tr>

			</table>

			<input type="hidden" name="action" value="ttsaudio_plugin_data_save" />
			<?php wp_nonce_field( 'ttsaudio_plugin_options', 'secure' ); ?>

		</form>
	</div>
	<?php
}

add_action( 'admin_footer', 'ttsaudio_option_footer_scripts', 100 );
function ttsaudio_option_footer_scripts(){
?>
	<script>
	jQuery(document).ready(function($){
		$('form#ttsaudio_form').submit(function() {
			var spinner = 	$('form#ttsaudio_form .spinner');
			spinner.css('visibility', 'visible');
			var data = jQuery(this).serialize();
			$.post(ajaxurl, data, function(response) {
				if(response == 1) {
					show_message(1);
					window.setTimeout(function(){
						$('#saved').fadeOut(300);
					}, 2000);
				} else {
					show_message(2);
					window.setTimeout(function(){
						$('#saved').fadeOut(300);
					}, 2000);
				}
				spinner.css('visibility', 'hidden');
			});
			return false;
		});

		function show_message(n) {
			if(n == 1) $('#saved').html('<div id="message" class="updated fade"><p><strong><?php _e('Options saved','ttsaudio');?></strong></p></div>').show();
			else $('#saved').html('<div id="message" class="error fade"><p><strong><?php _e('Options could not be saved','ttsaudio');?></strong></p></div>').show();
		}

		}); //end doc ready
	</script>
<?php
}
