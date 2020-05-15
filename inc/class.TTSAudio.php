<?php
class TTSAudio{

  public static $prefix = '_ttsaudio_';
  public $options;
  public $voices;
  private $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

  function __construct(){
    $this->prefix = self::$prefix;
    $this->options = get_option( ttsaudio_option_name );
    $this->voices = array('de-DE_BirgitVoice'=>'Birgit: Standard German of Germany (Standarddeutsch) female voice.','de-DE_DieterVoice'=>'Dieter: Standard German of Germany (Standarddeutsch) male voice.','en-GB_KateVoice'=>'Kate: British English female voice.','en-US_AllisonVoice'=>'Allison: American English female voice.','en-US_LisaVoice'=>'Lisa: American English female voice.','en-US_MichaelVoice'=>'Michael: American English male voice.','es-ES_EnriqueVoice'=>'Enrique: Castilian Spanish (español castellano) male voice.','es-ES_LauraVoice'=>'Laura: Castilian Spanish (español castellano) female voice.','es-LA_SofiaVoice'=>'Sofia: Latin American Spanish (español latinoamericano) female voice.','es-US_SofiaVoice'=>'Sofia: North American Spanish (español norteamericano) female voice.','fr-FR_ReneeVoice'=>'Renee: French (français) female voice.','it-IT_FrancescaVoice'=>'Francesca: Italian (italiano) female voice.','ja-JP_EmiVoice'=>'Emi: Japanese (日本語) female voice.','pt-BR_IsabelaVoice'=>'Isabela: Brazilian Portuguese (português brasileiro) female voice.','female'=>'Thu Dung: Vietnamese (female - northern)','male'=>'Cao Chung: Vietnamese (male - northern)','ngoclam'=>'Ngoc Lam: Vietnamese (female - central)','hatieumai'=>'Ha Tieu Mai: Vietnamese (female - southern)');
  }

  public function PlyrSkin( $folder ){
		if($folder == '') return;

    $output = array();
		if(file_exists($folder)){
			foreach (new DirectoryIterator( $folder.'/') as $file) {
        if($file->isDot()) continue;
        $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
				$output[$name]= ucwords(str_replace('-',' - ',$name ));
			}
		}

    return $output;
	}

  public function ttsDownloadMP3( $text, $voice ){

    if(!file_exists($this->options['mp3_dir'])) wp_mkdir_p( $this->options['mp3_dir'] );

    if( $voice=='female' ||$voice=='male' || $voice=='ngoclam' || $voice=='hatieumai'){
      $url = $this->ttsFPT($text, $voice);
      $filename = basename($url);

    }else{

      $url = $this->ttsWatson($text, $voice);
      $filename = $voice . '-' . md5($text).'.mp3';
    }

    $filepath = $this->options['mp3_dir'] . '/' . $filename;
    $fp = fopen($filepath, 'w+');
    $ch = curl_init(str_replace(" ","%20", $url));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $server_output = curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    return $filename;
    exit;
  }

  private function ttsWatson ($text, $voice = 'en-US_AllisonVoice'){
    if($text === '') return;
    $text = urlencode($text);
    $url = 'https://text-to-speech-demo.ng.bluemix.net/api/synthesize?accept=audio/mp3';
    $url.= '&text='.$text.'&voice='.$voice;

    return $url;
  }

  private function AcronymFPT($str) {
		$search = array('CLB','HLV','UBND','TP','THPT','THCS','NXB','BCH','QDND','LHQ','ANTT','CNTT','GD–ĐT','HĐQT','thế kỷ XX','thế kỷ XXI');
		$replace = array('câu lạc bộ','huấn luyện viên','uỷ ban nhân dân','thành phố','trung học phổ thông','trung học cơ sở','nhà xuất bản','ban chấp hành','quân đội nhân dân','Liên Hiệp Quốc','An ninh trật tự','Công nghệ thông tin','Giáo dục và Đào tạo','Hội đồng quản trị','thế kỷ hai mươi','thế kỷ hai mốt');
		$str = str_replace($search, $replace, $str);
		return $str;
	}

  private function ttsFPT( $text, $voice = 'female', $speed = 0){
		$text = $this->AcronymFPT ($text);
		$headers = [
			'api_key: '.$this->options['fpt_api_key'],
			'voice: '.$voice,
			'speed: '.$speed,
			'prosody: 1',
			'Cache-Control: no-cache'
		];

		$ch = curl_init('http://api.openfpt.vn/text2speech/v4');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); //set request type POST or GET
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $text );  //Post Fields
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		$server_output = curl_exec ($ch);
		curl_close ($ch);

    $server_output = json_decode( $server_output );
    $seconds = ceil(strlen($text)/130);
    sleep($seconds);

    return $server_output->async;
  }

  public function ttsMP3Output( $post_id ){
    $settings = get_post_meta( $post_id, $this->prefix.'settings', true );
    if (!isset($settings['mp3']) || FALSE === get_post_status( $post_id ) )  exit;

    $filepath = $this->options['mp3_dir'] .'/' . $settings['mp3'];
    if(file_exists($filepath)) $this->smartReadFile($filepath, $settings['mp3']);
    else echo 'File Does Not Exist!';
    exit;
  }

  private function smartReadFile($location, $filename, $mimeType = 'audio/mpeg') {
    if (!file_exists($location)) exit;

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

    $status  = get_post_meta( get_the_ID(), $this->prefix.'status', true );
    if(is_singular() && $status == 'enable') {
      $options = get_option( ttsaudio_option_name );
      $settings  = get_post_meta( get_the_ID(), $this->prefix.'settings', true );
      if($settings['custom_audio']) $mp3_url = $settings['custom_audio'];
      else $mp3_url = add_query_arg( array('audio' => get_the_ID()) , get_page_link($options['page_id']) );

      $custom_content .= '<p><div class="ttsaudio-player ttsaudio-'.$options['plyr_skin'].'"><audio id="plyr_'.get_the_ID().'" controls><source src="'.$mp3_url.'" type="audio/mp3"></audio></div></p>';

      $custom_content .= $content;
      return $custom_content;
    }
    else return $content;
  }

  public function template_include($original_template) {
    if( is_page( $this->options['page_id'] ))
    {
      $post_id = filter_input(INPUT_GET, "audio", FILTER_SANITIZE_NUMBER_INT);
      $this->ttsMP3Output( $post_id );
      die;
    } else return $original_template;
  }

  public function footer_script() {
    if(!is_singular()) return;
    ?>
    <script type='text/javascript'>
      plyr.setup('#plyr_<?php the_ID();?>');
    </script>
    <?php
  }

}