<?php
function ttsaudio_startSession() {
    if(!session_id()) {
      session_start();
    }
}
add_action('init', 'ttsaudio_startSession', 1);

add_action( 'admin_action_ttsaudio_options', 'ttsaudio_admin_action' );
function ttsaudio_admin_action(){

		$post_form = filter_input_array( INPUT_POST );

		$options = [];
		if( is_array(get_option(TTSAUDIO_OPTION)) ) $options = get_option(TTSAUDIO_OPTION);

		if(!empty($post_form)) {
			$diff = array_diff($options, $post_form);
			$diff2 = array_diff($post_form, $options);
			$diff = array_merge($diff, $diff2);
		} else $diff = array();

		$updated = 'false';
		if(update_option(TTSAUDIO_OPTION, $post_form)) $updated = 'true';

		wp_redirect( add_query_arg( 'ttsaudio_updated', $updated, $_SERVER['HTTP_REFERER'] ) );
    exit();
}

add_action( 'admin_menu', 'ttsaudio_admin_menu' );
function ttsaudio_admin_menu(){
    add_menu_page( __( 'TTS Audio Options', 'ttsaudio' ), __( 'TTS Audio', 'ttsaudio' ), 'manage_options', 'ttsaudio_options', 'ttsaudio_options_do_page', 'dashicons-controls-volumeon');
}

function ttsaudio_options_do_page(){
	$options = get_option( TTSAUDIO_OPTION );
	$tts = new TTSAudio;

  $ttsaudio_updated_status = filter_input(INPUT_GET, "ttsaudio_updated", FILTER_SANITIZE_STRING);
	if (!empty( $ttsaudio_updated_status )) {

		if( $ttsaudio_updated_status === 'true' )
			$_SESSION['msg'] = '<div class="notice notice-success is-dismissible"><p><strong>'.__('Options saved','ttsaudio').'</strong></p></div>';
		else $_SESSION['msg'] = '<div class="notice notice-warning is-dismissible"><p><strong>'.__('Options could not be saved or not change','ttsaudio').'</strong></p></div>';

		echo '<script>window.location.replace("'.$_SERVER['HTTP_REFERER'].'");</script>';
		exit;
	}

?>
<div class="wrap ttsaudio-options">
	<?php printf('<h2>%1$s <a class="buy-pro dashicons-before dashicons-cart" href="%2$s" title="%3$s" >%3$s</a></h2>', __( 'TTS Audio Options', 'ttsaudio' ), 'https://gearthemes.com/ttsaudio', 'buy TTSAudio Pro');?>
	<?php echo $_SESSION['msg']; unset($_SESSION['msg']);?>

	<form method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">
		<table class="form-table">
			<?php do_action('ttsaudio_option_fields');?>

			<tr valign="top"><th scope="row"><?php _e( 'Skin', 'ttsaudio' ); ?></th>
				<td>
					<select name="plyr_skin" class="regular-text">
						<?php
						$skins = $tts->PlyrSkin();
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
					<input type="submit" class="of-btn-save button-primary" value="<?php _e( 'Save Options', 'ttsaudio' ); ?>" />
				</td>
			</tr>

		</table>
		<input type="hidden" name="action" value="ttsaudio_options" />
	</form>
</div>
<?php
}
