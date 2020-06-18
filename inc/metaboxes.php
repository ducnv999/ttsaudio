<?php

//Add default meta box
add_action('add_meta_boxes', 'add_custom_meta_box_post');
function add_custom_meta_box_post($post) {
  add_meta_box('sections_meta_box', 'TTS Audio Options', 'show_custom_meta_box', 'post' );
}

add_action( 'save_post', 'ttsaudio_save' );
function ttsaudio_save( $post_id ){
  $prefix = TTSAudio::$prefix;

  if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')) return;

  $status = sanitize_text_field( $_POST[$prefix . 'status'] );
  update_post_meta( $post_id, $prefix . 'status' , $status );
  $tts_settings = $_POST[$prefix . 'settings'];
  $tts_settings['text'] = sanitize_textarea_field($tts_settings['text']);

  update_post_meta( $post_id, $prefix . 'settings' , $tts_settings );

  if($status=='delete') {
    //unlink(ttsaudio_mp3_dir.$tts_settings['mp3']);
    delete_post_meta($post_id, $prefix . 'settings');
  }

}

function show_custom_meta_box( $post ) {
  $prefix = TTSAudio::$prefix;

  $status = get_post_meta( $post->ID, $prefix . 'status', true );
  $settings = get_post_meta( $post->ID, $prefix . 'settings', true );

  $select_options = array('disable','enable');
  if($status == 'enable') $select_options = array('disable','enable','delete');

  $html = '<table id="ttsaudio_form" class="form-table"><tbody><tr>';
  $html .= '<th>TTS Audio Status</th><td><select id="'.$prefix.'status" name="'.$prefix.'status">';
  foreach ($select_options as $value) {

    $html .= '<option value="' . $value . '" '.selected( $status, $value, false ).'>' . ucwords($value) . '</option>';
  }
  $html .= '</select></td></tr>';

  if($status == 'enable') $html .= add_custom_meta_box( $post->ID );

  $html .= '</tbody></table>';

  echo $html;
}

//Our custom meta box will be loaded on ajax
function add_custom_meta_box( $post_id ){
  $options = get_option( ttsaudio_option_name );
  $tts = new TTSAudio;

  //Settings
  $settings = array();
  if(get_post_meta( $post_id, $tts->prefix . 'settings', true ))
  $settings = get_post_meta( $post_id, $tts->prefix . 'settings', true );

  //Voices
  $html = '<tr class="more"><th>Voice</th><td><select id="ttsaudio_voice" name="'.$tts->prefix.'settings[voice]">';
  $selected = $tts->options['default_voice'];
  if(!empty($settings['voice'])) $selected = $settings['voice'];
  foreach ($tts->voices as $key => $value) {
    $html .= '<option value="' . $key . '" '.selected( $selected, $key, false ).'>' . $value . '</option>';
  }
  $html.= '</select><p class="howto">You can set Default Voice in <a href="'.menu_page_url('ttsaudio_options',0).'" target="_blank">TTS Audio Options</a>.</p></td></tr>';

  //Text
  $html.= '<tr class="more"><th>Text (to speak)</th><td>';
  $html.= '<textarea class="large-text" id="ttsaudio_text" name="'.$tts->prefix.'settings[text]" cols="60" rows="10">'.$settings['text'].'</textarea>';
  $html.= '<p id="textarea_length" class="howto">Separate paragraph with double blank lines.</p>';
  $html.= '<p class="alignleft"><input id="CreateAudioBtn" type="button" class="button-primary" value="'.__( 'Create Audio', 'sampletheme' ).'" /><span class="spinner" id="spinner"></span> <span id="res_text"></span></p><br class="clear">';

  $html.= '<input type="hidden" id="ttsaudio_mp3" name="'.$tts->prefix.'settings[mp3]" value="'.$settings['mp3'].'" ></td></tr>';

  //Custom audio
  $html.= '<tr class="more"><th>Custom Audio</th><td>';
  $html.= '<input id="ttsaudio_custom" type="text" class="large-text" name="'.$tts->prefix.'settings[custom_audio]" value="'.$settings['custom_audio'].'" >';
  $html.= '<p class="howto">Ex: http://domain.com/sound.mp3</p></td></tr>';

  return $html;
}

//Call ajax
add_action('wp_ajax_addStructureBox', 'addStructureBox');
function addStructureBox() {
  $post_id = filter_input(INPUT_POST, "post_id", FILTER_SANITIZE_NUMBER_INT);
  check_ajax_referer('ttsaudio_meta_box_'.$post_id, 'security_'.$post_id);
  echo add_custom_meta_box($post_id);
  exit;
}

add_action('wp_ajax_ttsMakeMP3', 'ttsMakeMP3');
function ttsMakeMP3() {
  $tts = new TTSAudio;

  $post_id = filter_input(INPUT_POST, "post_id", FILTER_SANITIZE_NUMBER_INT);
  check_ajax_referer('ttsaudio_meta_box_'.$post_id, 'security_'.$post_id);

  $text = filter_input(INPUT_POST, "text", FILTER_SANITIZE_STRING);
  $voice = filter_input(INPUT_POST, "voice", FILTER_SANITIZE_STRING);
  $custom_audio = filter_input(INPUT_POST, "custom_audio", FILTER_SANITIZE_STRING);

  $meta_settings = get_post_meta( $post_id, $tts->prefix . 'meta_settings', true );
  unlink(ttsaudio_mp3_dir.$meta_settings['mp3']);

  $filename = $tts->ttsDownloadMP3($text, $voice);
  update_post_meta( $post_id, $tts->prefix.'status', 'enable');
  $settings = array('voice' => $voice, 'text' => $text,'mp3' => $filename, 'custom_audio' =>$custom_audio);
  update_post_meta( $post_id, $tts->prefix.'settings', $settings);

  echo $filename;
  exit;
}

//Add script
add_action('admin_head','ajax_script');
function ajax_script(){
  global $post;

  $ajax_nonce = wp_create_nonce( 'ttsaudio_meta_box_'.$post->ID );
?>
  <script>
  jQuery(document).ready(function ($) {

    $(document).on('change keyup paste', '#ttsaudio_text', function (e) {
      var currentVal = $(this).val().length + ' characters';
      $('#textarea_length').text(currentVal);
    });

    $('#<?php echo TTSAudio::$prefix;?>status').change(function () {
      var status = $(this).val();
      $.post(ajaxurl, {action: 'addStructureBox', post_id: <?php echo $post->ID;?>, security_<?php echo $post->ID;?>: '<?php echo $ajax_nonce;?>'}, function (data) {
        if(status  == 'enable') $('table#ttsaudio_form').append(data);
        else $('table#ttsaudio_form').find('tr.more').remove();
      });
    });

    $(document).on('click', '#CreateAudioBtn', function (e) {
      if($('#ttsaudio_text').val() == '') {
        alert('<?php _e('Please enter text!','ttsaudio');?>');
        return false;
      }
      $('#res_text').empty();
      var spinner = 	$('#spinner');
      spinner.css('visibility', 'visible');
      $.post(ajaxurl, {action: 'ttsMakeMP3', post_id: <?php echo $post->ID;?>, text: $('#ttsaudio_text').val(), voice: $('#ttsaudio_voice').val(), custom_audio: $('#ttsaudio_custom').val(), security_<?php echo $post->ID;?>: '<?php echo $ajax_nonce;?>' }, function (data) {
        spinner.css('visibility', 'hidden');
        $('#ttsaudio_mp3').val(data);
        $('#res_text').html('<font color="green"><b><?php _e('Done!','ttsaudio');?></b></font>');
      });
    });

  });
  </script>
<?php
}
