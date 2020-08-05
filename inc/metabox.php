<?php

/**
 * Generated by the WordPress Meta Box generator
 * at http://jeremyhixon.com/tool/wordpress-meta-box-generator/
 */
class TTSAudio_MetaBoxes {

	public function __construct(){

		add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_styles_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
		add_action( 'wp_ajax_ttsaudio_create_audio', array( $this, 'create_audio' ) );

	}

	public function meta_box_styles_scripts() {
		global $id, $post;

    if ( isset( get_current_screen()->base ) && 'post' !== get_current_screen()->base ) {
      return;
    }
    if ( isset( get_current_screen()->post_type )
         && !in_array( get_current_screen()->post_type, ['post', 'page'] ) ) {
      return;
    }

		$post_id = isset( $post->ID ) ? $post->ID : (int) $id;

		wp_enqueue_script( 'ttsaudio-ajax-script', plugin_dir_url( __DIR__ ) . 'assets/js/metaboxes.js', array('jquery') );
		wp_enqueue_media( array( 'post' => $post_id ) );

	}


	public function add_meta_boxes() {
		add_meta_box(
			'ttsaudio_metaboxes',
			__( 'TTSAudio Option', 'ttsaudio' ),
			array( $this, 'create_meta_boxes'),
			['post', 'page'],
			'normal',
			'high'
		);
	}

	public function mb_get_meta( $value ) {
		global $post;

		$field = get_post_meta( $post->ID, $value, true );
		if ( ! empty( $field ) ) {
			return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
		} else {
			return false;
		}
	}

	public function mb_get_post_id() {
		global $post;

		return $post->ID;
	}

	public function create_meta_boxes( $post) {

		$options = get_option( 'ttsaudio_options' );
	  $tts = new TTSAudio;
		wp_nonce_field( '_ttsaudio_option_nonce', 'ttsaudio_option_nonce' );
		?>

		<table class="form-table">
			<tbody>
				<tr class="ttsaudio_status_wrap">
					<th><label for="ttsaudio_status"><?php _e( 'TTS Audio', 'ttsaudio_option' ); ?></label></th>
					<td>
						<select name="ttsaudio_status" id="ttsaudio_status">
							<option value="disable" <?php selected( 'disable', $this->mb_get_meta('ttsaudio_status') );?>>Disable</option>
							<option value="enable" <?php selected( 'enable', $this->mb_get_meta('ttsaudio_status') );?>>Enable</option>
							<?php if( $this->mb_get_meta('ttsaudio_status') == 'enable'):?>
							<option value="delete" <?php selected( 'delete', $this->mb_get_meta('ttsaudio_status') );?>>Delete</option>
							<?php endif;?>
						</select>
						<input type="hidden" id="ttsaudio_status_security" value="<?php echo wp_create_nonce( "status-2020xxyy" ); ?>" />
					</td>
				</tr>
			</tbody>
			<?php $display = ($this->mb_get_meta('ttsaudio_status') == 'enable') ? '' : 'none'; ?>
			<tbody style="display: <?php echo $display;?>">
				<tr>
					<th><label for="ttsaudio_option_voice"><?php _e( 'Voice', 'ttsaudio_option' ); ?></label></th>
					<td>
						<select name="ttsaudio_option_voice" id="ttsaudio_option_voice">
							<?php foreach ($tts->voices as $key => $value) {?>
							<option value="<?php esc_attr_e($key);?>" <?php selected( $key, $this->mb_get_meta('ttsaudio_option_voice') );?>><?php esc_html_e($value);?></option>
							<?php } ?>
						</select>
						<p>You can set Default Voice in <a href="<?php menu_page_url('ttsaudio');?>" target="_blank">TTS Audio Options</a></p>
					</td>
				</tr>
				<tr>
					<th><label for="ttsaudio_option_text_to_speech"><?php _e( 'Text (to speech)', 'ttsaudio_option' ); ?></label></th>
					<td>
						<textarea class="large-text" rows="10" name="ttsaudio_option_text_to_speech" id="ttsaudio_option_text_to_speech" ><?php echo $this->mb_get_meta( 'ttsaudio_option_text_to_speech' ); ?></textarea>
						<p><?php _e( 'Separate paragraph with double blank lines.', 'ttsaudio' );?></p>
						<p class="alignleft">
							<input type="hidden" name="ttsaudio_option_mp3" id="ttsaudio_option_mp3" value="<?php echo $this->mb_get_meta( 'ttsaudio_option_mp3' ); ?>" />
							<input id="ttsaudio_create_mp3" data-security="<?php echo wp_create_nonce( 'create-audio-special-string' ); ?>" type="button" class="button-primary" value="<?php _e( 'Create Audio', 'ttsaudio' );?>" />
							<span class="ajax_result"></span>
							<span class="audio_file"><?php echo $this->mb_get_meta( 'ttsaudio_option_mp3' ); ?></span>
						</p><br class="clear">
					</td>
				</tr>
				<tr>
					<th><label for="ttsaudio_option_custom_audio_"><?php _e( 'Custom Audio', 'ttsaudio' ); ?></label></th>
					<td>
						<input class="large-text" type="url" id="ttsaudio_option_custom_audio" name="ttsaudio_option_custom_audio" value="<?php echo $this->mb_get_meta( 'ttsaudio_option_custom_audio' ); ?>" />
						<p><?php _e( 'Eg. http://domain.com/sound.mp3.', 'ttsaudio' );?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	public function save_meta_boxes( $post_id ) {

		//Check security
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['ttsaudio_option_nonce'] ) || ! wp_verify_nonce( $_POST['ttsaudio_option_nonce'], '_ttsaudio_option_nonce' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		//Save any status
		if( isset( $_POST['ttsaudio_status'] ) && !empty( sanitize_text_field( $_POST['ttsaudio_status'] ) ) )
			update_post_meta( $post_id, 'ttsaudio_status', sanitize_text_field( $_POST['ttsaudio_status'] ) );

		//Return if status is disable
		if ( isset( $_POST['ttsaudio_status'] ) && 'disable' == sanitize_text_field( $_POST['ttsaudio_status'] ) ) return;

		//Delete TTS Audio
		if( isset( $_POST['ttsaudio_status'] ) && 'delete' == sanitize_text_field( $_POST['ttsaudio_status'] ) ){

			delete_post_meta( $post_id, 'ttsaudio_status');
			delete_post_meta( $post_id, 'ttsaudio_option_voice');
			delete_post_meta( $post_id, 'ttsaudio_option_text_to_speech');
			delete_post_meta( $post_id, 'ttsaudio_option_custom_audio');

			$tts = new TTSAudio;
			$mp3_file_path = $tts->ttsaudio_upload_dir.'/' . get_post_meta( $post_id, 'ttsaudio_option_mp3', true );
			error_log($mp3_file_path);
			if( is_file( $mp3_file_path ) ) unlink( $mp3_file_path );
			delete_post_meta( $post_id, 'ttsaudio_option_mp3');

			return;
		}

		//Updates
		if( isset( $_POST['ttsaudio_status'] ) && !empty( sanitize_text_field( $_POST['ttsaudio_status'] ) ) )
			update_post_meta( $post_id, 'ttsaudio_status', sanitize_text_field( $_POST['ttsaudio_status'] ) );

		if ( isset( $_POST['ttsaudio_option_voice'] ) && !empty( sanitize_text_field( $_POST['ttsaudio_option_voice'] ) ) )
			update_post_meta( $post_id, 'ttsaudio_option_voice', sanitize_text_field( $_POST['ttsaudio_option_voice'] ) );

		if ( isset( $_POST['ttsaudio_option_text_to_speech'] ) && !empty( sanitize_text_field( $_POST['ttsaudio_option_text_to_speech'] ) ) )
			update_post_meta( $post_id, 'ttsaudio_option_text_to_speech', sanitize_textarea_field( $_POST['ttsaudio_option_text_to_speech'] ) );

		if ( isset( $_POST['ttsaudio_option_mp3'] ) && !empty( sanitize_text_field( $_POST['ttsaudio_option_mp3'] ) ) )
			update_post_meta( $post_id, 'ttsaudio_option_mp3', sanitize_text_field( $_POST['ttsaudio_option_mp3'] ) );

		if ( isset( $_POST['ttsaudio_option_custom_audio'] ) && !empty( esc_url_raw( $_POST['ttsaudio_option_custom_audio'] ) ) )
			update_post_meta( $post_id, 'ttsaudio_option_custom_audio', esc_url_raw( $_POST['ttsaudio_option_custom_audio'] ) );

		return;
	}

	public function create_audio() {

	    check_ajax_referer( 'create-audio-special-string', 'security' );

			$tts = new TTSAudio;
			$post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

			//Remove old media file
			$mp3_file_path = $tts->ttsaudio_upload_dir.'/' . get_post_meta( $post_id, 'ttsaudio_option_mp3', true );
			if( is_file( $mp3_file_path ) ) unlink( $mp3_file_path );
			delete_post_meta( $post_id, 'ttsaudio_option_mp3');

			//Create new media file
			$filename = $tts->ttsDownloadMP3( sanitize_textarea_field( $_POST['text'] ), sanitize_text_field( $_POST['voice'] ) );
			update_post_meta( $post_id, 'ttsaudio_option_mp3', $filename);

			echo $filename;

	    die;
	}

} //END CLASSS

if ( is_admin() )
	$metaboxes = new TTSAudio_MetaBoxes();
