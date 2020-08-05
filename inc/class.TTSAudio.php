<?php

class TTSAudio{

  public $options;
  public $voices;
  public $ttsaudio_upload_dir;

  function __construct(){

    $this->options = get_option( 'ttsaudio_options' );

    $default_voices = ['en-US_AllisonVoice' => 'American English (en-US): Allison (female, expressive, transformable)',
    'en-US_AllisonV3Voice' => 'American English (en-US): AllisonV3 (female, enhanced dnn)',
    'en-US_EmilyV3Voice' => 'American English (en-US): EmilyV3 (female, enhanced dnn)',
    'en-US_HenryV3Voice' => 'American English (en-US): HenryV3 (male, enhanced dnn)',
    'en-US_KevinV3Voice' => 'American English (en-US): KevinV3 (male, enhanced dnn)',
    'en-US_LisaVoice' => 'American English (en-US): Lisa (female, transformable)',
    'en-US_LisaV3Voice' => 'American English (en-US): LisaV3 (female, enhanced dnn)',
    'en-US_MichaelVoice' => 'American English (en-US): Michael (male, transformable)',
    'en-US_MichaelV3Voice' => 'American English (en-US): MichaelV3 (male, enhanced dnn)',
    'en-US_OliviaV3Voice' => 'American English (en-US): OliviaV3 (female, enhanced dnn)',
    'ar-AR_OmarVoice' => 'Arabic (ar-AR): Omar (male)',
    'pt-BR_IsabelaVoice' => 'Brazilian Portuguese (pt-BR): Isabela (female)',
    'pt-BR_IsabelaV3Voice' => 'Brazilian Portuguese (pt-BR): IsabelaV3 (female, enhanced dnn)',
    'en-GB_CharlotteV3Voice' => 'British English (en-GB): CharlotteV3 (female, enhanced dnn)',
    'en-GB_JamesV3Voice' => 'British English (en-GB): JamesV3 (male, enhanced dnn)',
    'en-GB_KateVoice' => 'British English (en-GB): Kate (female)',
    'en-GB_KateV3Voice' => 'British English (en-GB): KateV3 (female, enhanced dnn)',
    'es-ES_EnriqueVoice' => 'Castilian Spanish (es-ES): Enrique (male)',
    'es-ES_EnriqueV3Voice' => 'Castilian Spanish (es-ES): EnriqueV3 (male, enhanced dnn)',
    'es-ES_LauraVoice' => 'Castilian Spanish (es-ES): Laura (female)',
    'es-ES_LauraV3Voice' => 'Castilian Spanish (es-ES): LauraV3 (female, enhanced dnn)',
    'zh-CN_LiNaVoice' => 'Chinese, Mandarin (zh-CN): LiNa (female)',
    'zh-CN_WangWeiVoice' => 'Chinese, Mandarin (zh-CN): WangWei (Male)',
    'zh-CN_ZhangJingVoice' => 'Chinese, Mandarin (zh-CN): ZhangJing (female)',
    'nl-NL_EmmaVoice' => 'Dutch (nl-NL): Emma (female)',
    'nl-NL_LiamVoice' => 'Dutch (nl-NL): Liam (male)',
    'fr-FR_NicolasV3Voice' => 'French (fr-FR): NicolasV3 (male, enhanced dnn)',
    'fr-FR_ReneeVoice' => 'French (fr-FR): Renee (female)',
    'fr-FR_ReneeV3Voice' => 'French (fr-FR): ReneeV3 (female, enhanced dnn)',
    'de-DE_BirgitVoice' => 'German (de-DE): Birgit (female)',
    'de-DE_BirgitV3Voice' => 'German (de-DE): BirgitV3 (female, enhanced dnn)',
    'de-DE_DieterVoice' => 'German (de-DE): Dieter (male)',
    'de-DE_DieterV3Voice' => 'German (de-DE): DieterV3 (male, enhanced dnn)',
    'de-DE_ErikaV3Voice' => 'German (de-DE): ErikaV3 (female, enhanced dnn)',
    'it-IT_FrancescaVoice' => 'Italian (it-IT): Francesca (female)',
    'it-IT_FrancescaV3Voice' => 'Italian (it-IT): FrancescaV3 (female, enhanced dnn)',
    'ja-JP_EmiVoice' => 'Japanese (ja-JP): Emi (female)',
    'ja-JP_EmiV3Voice' => 'Japanese (ja-JP): EmiV3 (female, enhanced dnn)',
    'ko-KR_YoungmiVoice' => 'Korean (ko-KR): Youngmi (female)',
    'ko-KR_YunaVoice' => 'Korean (ko-KR): Yuna (female)',
    'es-LA_SofiaVoice' => 'Latin American Spanish (es-LA): Sofia (female)',
    'es-LA_SofiaV3Voice' => 'Latin American Spanish (es-LA): SofiaV3 (female, enhanced dnn)',
    'es-US_SofiaVoice' => 'North American Spanish (es-US): Sofia (female)',
    'es-US_SofiaV3Voice' => 'North American Spanish (es-US): SofiaV3 (female, enhanced dnn)'];

    $vi_voices = ['vi-leminh'=>'Lê Minh (Nam miền Bắc)','vi-banmai'=>'Ban Mai (Nữ miền Bắc)','vi-thuminh'=>'Thu Minh (Nữ miền Bắc)','vi-giahuy'=>'Gia Huy (Nam miền Trung)','vi-myan'=>'Mỹ An (Nữ miền Trung)','vi-lannhi'=>'Lan Nhi (Nữ miền Nam)','vi-linhsan'=>'Linh San (Nữ miền Nam)','vi-male'=>'Cao Chung (Nam miền Bắc)','vi-female'=>'Thu Dung (Nữ miền Bắc)','vi-hatieumai'=>'Hà Tiểu Mai (Nữ miền Nam)'];

    if(!empty($this->options['fpt_api_key'])) $default_voices = $default_voices + $vi_voices;

    $this->voices = $default_voices;

    $this->ttsaudio_upload_dir = wp_upload_dir()['basedir'].'/ttsaudio';

  }

  public function PlyrSkin(){
    $skin_arr = ['default', 'dark', 'amber', 'apple', 'canva', 'mauve'];

    $output_skins = [];
    foreach ($skin_arr as $skin) {
      $output_skins[$skin] = ucwords(str_replace('-',' - ',$skin ));
    }

    $skins = apply_filters('ttsaudio_skins', $output_skins);
    return $skins;
	}

  public function filesystem(){
      global $wp_filesystem;

      if ( is_null( $wp_filesystem ) ) {
          require_once ABSPATH . '/wp-admin/includes/file.php';
          WP_Filesystem();
      }

      return $wp_filesystem;
  }

  public function ttsDownloadMP3( $text, $voice ){

    if(!file_exists( $this->ttsaudio_upload_dir )) wp_mkdir_p( $this->ttsaudio_upload_dir );

    if( substr($voice, 0, 2) == 'vi' ){
      $voice = substr($voice, 3);
      $url = $this->ttsFPT($text, $voice);
      $filename = basename($url);

    }else{

      $url = $this->ttsWatson($text, $voice);
      $filename = $voice . '-' . md5($text).'.mp3';
    }

    $args = array(
      'timeout'   => 500,
      'sslverify' => false
    );
    
    $body = wp_remote_retrieve_body( wp_remote_get( esc_url_raw($url), $args ) );

    $filepath = $this->ttsaudio_upload_dir . '/' . $filename;
    $wp_filesystem = $this->filesystem();
    $wp_filesystem->put_contents($filepath, $body);

    return $filename;
    exit;
  }

  private function ttsWatson ($text, $voice = 'en-US_AllisonVoice'){
    if( empty($text) ) return;

    $text = urlencode($text);
    $url = 'https://text-to-speech-demo.ng.bluemix.net/api/v3/synthesize?accept=audio%2Fmp3&download=true';
    $url.= '&text='.$text.'&voice='.$voice;

    return $url;
  }

  public function ttsFPT( $text, $voice = 'female', $speed = 0){

    $headers = [
      'api-key' => $this->options['fpt_api_key'],
      'voice' => $voice,
      'speed' => $speed
    ];

    $response = wp_remote_post( esc_url_raw('https://api.fpt.ai/hmi/tts/v5'), ['body' => $text, 'headers' => $headers] );
    $api_response = json_decode( wp_remote_retrieve_body( $response ), true );

    return ttsCheckUrl($api_response['async']);
  }

  public function ttsMP3Output( $post_id ){

    $mp3_file = get_post_meta( $post_id, 'ttsaudio_option_mp3', true ) ? : '';

    if (empty( $mp3_file ) || FALSE === get_post_status( $post_id ) )  return;

    $filepath = $this->ttsaudio_upload_dir .'/' . $mp3_file;
    if(!file_exists($filepath)) $filepath = TTSAUDIO_DIR . 'assets/the_audio_file_does_not_exist.mp3';

    $this->smartReadFile($filepath, $mp3_file);

    return;

  }

  private function smartReadFile($location, $filename, $mimeType = 'audio/mpeg') {
    if (!is_file($location)) return;

    $size	= filesize($location);
    $time	= date('r', filemtime($location));

    $fm		= @fopen($location, 'rb');
    if (!$fm)
    {
      header ("HTTP/1.1 505 Internal server error");
      return;
    }

    $begin	= 0;
    $end	= $size - 1;

    if (isset($_SERVER['HTTP_RANGE'])) {
      if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
        $begin	= intval($matches[1]);
        if (!empty($matches[2])) $end	= intval($matches[2]);
      }
    }

    if (isset($_SERVER['HTTP_RANGE'])) header('HTTP/1.1 206 Partial Content');
    else header('HTTP/1.1 200 OK');

    header("Content-Type: $mimeType");
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Accept-Ranges: bytes');
    header('Content-Length:' . (($end - $begin) + 1));
    if (isset($_SERVER['HTTP_RANGE'])) header("Content-Range: bytes $begin-$end/$size");

    header("Content-Disposition: inline; filename=$filename");
    header("Content-Transfer-Encoding: binary");
    header("Last-Modified: $time");

    $cur	= $begin;
    fseek($fm, $begin, 0);

    while(!feof($fm) && $cur <= $end && (connection_status() == 0))
    {
      print fread($fm, min(1024 * 16, ($end - $cur) + 1));
      $cur += 1024 * 16;
    }
  }

  public function ttsAudioContent( $content ) {

    $status  = get_post_meta( get_the_ID(), 'ttsaudio_status', true ) ? : '';
    if( !is_singular() || $status !== 'enable' ) return $content;

    $mp3_url = get_post_meta( get_the_ID(), 'ttsaudio_option_custom_audio', true ) ? : add_query_arg( array('ttsaudio' => get_the_ID()) , home_url() );
    if( empty( $mp3_url ) ) return $content;

    $cpr = sprintf('<a class="ttsaudio-plyr--single__info" title="%s" href="%s" target="_blank"></a>', 'TTS Audio by GearThemes', 'https://gearthemes.com');
    $string_html = '<div class="ttsaudio-plyr ttsaudio-plyr--%s ttsaudio-plyr--single"><audio id="plyr_%d" controls><source src="%s" type="audio/mp3" /></audio>%s</div>';

    return $content . sprintf($string_html, esc_attr( $this->options['plyr_skin'] ), get_the_ID(), esc_url_raw($mp3_url), apply_filters('gt_player_copyrights', $cpr));
  }

  public function template_include($original_template) {
    $ttsaudio = get_query_var( 'ttsaudio' );

    if( $ttsaudio ){
      $this->ttsMP3Output( $ttsaudio );
      exit;
    } else return $original_template;
  }

  public function single_script(){

    if( !is_singular() ) return;
    wp_add_inline_script( 'ttsaudio-plyr', 'plyr.setup(\'#plyr_'.get_the_ID().'\');' );

  }

  public static function author(){
    $output = '<div class="ttsaudio-plyr--playlist__author">';
    $output .=  sprintf('<a href="%1$s" title="%2$s" target="_blank">%2$s</a>', 'https://gearthemes.com', 'TTS Audio by GearThemes');;
    $output .= '</div>';
    return apply_filters('gt_widget_copyrights', $output);
  }

}


function ttsCheckUrl( $url ) {
  $file_headers = @get_headers($url);
  if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.0 404 Not Found') {
    usleep(10000);
    return ttsCheckUrl($url);
  }
  else return $url;
}
