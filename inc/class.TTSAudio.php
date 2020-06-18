<?php
class TTSAudio{

  public static $prefix = '_ttsaudio_';
  public $options;
  public $voices;

  function __construct(){
    $this->prefix = self::$prefix;
    $this->options = get_option( ttsaudio_option_name );
    $this->voices = array('de-DE_BirgitV3Voice'=>'Birgit: Standard German of Germany (Standarddeutsch) female voice.','de-DE_DieterV3Voice'=>'Dieter: Standard German of Germany (Standarddeutsch) male voice.','en-GB_KateV3Voice'=>'Kate: British English female voice.','en-US_AllisonV3Voice'=>'Allison: American English female voice.','en-US_LisaV3Voice'=>'Lisa: American English female voice.','en-US_MichaelV3Voice'=>'Michael: American English male voice.','es-ES_EnriqueV3Voice'=>'Enrique: Castilian Spanish (español castellano) male voice.','es-ES_LauraV3Voice'=>'Laura: Castilian Spanish (español castellano) female voice.','es-LA_SofiaV3Voice'=>'Sofia: Latin American Spanish (español latinoamericano) female voice.','es-US_SofiaV3Voice'=>'Sofia: North American Spanish (español norteamericano) female voice.','fr-FR_ReneeV3Voice'=>'Renee: French (français) female voice.','it-IT_FrancescaV3Voice'=>'Francesca: Italian (italiano) female voice.','ja-JP_EmiV3Voice'=>'Emi: Japanese (日本語) female voice.','pt-BR_IsabelaV3Voice'=>'Isabela: Brazilian Portuguese (português brasileiro) female voice.',
    'vi-leminh'=>'Lê Minh (Nam miền Bắc)',
    'vi-banmai'=>'Ban Mai (Nữ miền Bắc)',
    'vi-thuminh'=>'Thu Minh (Nữ miền Bắc)',
    'vi-giahuy'=>'Gia Huy (Nam miền Trung)',
    'vi-myan'=>'Mỹ An (Nữ miền Trung)',
    'vi-lannhi'=>'Lan Nhi (Nữ miền Nam)',
    'vi-linhsan'=>'Linh San (Nữ miền Nam)',
    'vi-male'=>'Cao Chung (Nam miền Bắc)',
    'vi-female'=>'Thu Dung (Nữ miền Bắc)',
    'vi-hatieumai'=>'Hà Tiểu Mai (Nữ miền Nam)'
    );
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

    if( substr($voice, 0, 2) == 'vi' ){
      $voice = substr($voice, 3);
      $url = $this->ttsFPT($text, $voice);
    }
    else $url = $this->ttsWatson($text, $voice);


    return $url;
    exit;
  }

  private function ttsWatson ($text, $voice = 'en-US_AllisonVoice'){
    if($text === '') return;
    $text = urlencode($text);
    $url = 'https://text-to-speech-demo.ng.bluemix.net/api/v3/synthesize?accept=audio/mp3';
    $url.= '&text='.$text.'&voice='.$voice;

    return $url;
  }

  private function AcronymFPT($str) {
		$search = array('CLB','HLV','UBND','TP','THPT','THCS','NXB','BCH','QDND','LHQ','ANTT','CNTT','GD–ĐT','HĐQT','thế kỷ XX','thế kỷ XXI');
		$replace = array('câu lạc bộ','huấn luyện viên','uỷ ban nhân dân','thành phố','trung học phổ thông','trung học cơ sở','nhà xuất bản','ban chấp hành','quân đội nhân dân','Liên Hiệp Quốc','An ninh trật tự','Công nghệ thông tin','Giáo dục và Đào tạo','Hội đồng quản trị','thế kỷ hai mươi','thế kỷ hai mốt');
		$str = str_replace($search, $replace, $str);
		return $str;
	}

  private function ttsFPT( $text, $voice = 'myan', $speed = 0){
		$text = $this->AcronymFPT ($text);
		$headers = [
			'api-key: '.$this->options['fpt_api_key'],
			'voice: '.$voice,
			'speed: '.$speed,
		];

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.fpt.ai/hmi/tts/v5",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $text,
      CURLOPT_HTTPHEADER => array(
        "api-key: ".$this->options['fpt_api_key'],
        "voice: ".$voice,
        "Content-Type: text/plain"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $server_output = json_decode( $response );

    return $server_output->async;
  }


  public function ttsAudioContent( $content ) {

    $status  = get_post_meta( get_the_ID(), $this->prefix.'status', true );
    if(is_singular() && $status == 'enable') {
      $options = get_option( ttsaudio_option_name );
      $settings  = get_post_meta( get_the_ID(), $this->prefix.'settings', true );

      if($settings['custom_audio']) $mp3_url = $settings['custom_audio'];
      else $mp3_url = $settings['mp3'];

      $custom_content .= '<p><div class="ttsaudio-player ttsaudio-'.$options['plyr_skin'].'"><audio id="plyr_'.get_the_ID().'" controls><source src="'.$mp3_url.'" type="audio/mp3"></audio></div></p>';

      $custom_content .= $content;
      return $custom_content;
    }
    else return $content;
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
