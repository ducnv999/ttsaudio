jQuery(function($){

  $('#ttsaudio_status').on('change', function () {
    var add_tbody = $(this).closest('tbody').next('tbody');
    if( $(this).val() == 'enable') add_tbody.show();
    else add_tbody.hide();

  });

  //Calculate characters length for textarea
  $(document).on('change keyup paste', '#ttsaudio_option_text_to_speech', function (e) {
    var currentVal = $(this).val().length + ' characters';
    $(this).next('p').text(currentVal);
  });

  //Create MP3 file
  $('#ttsaudio_create_mp3').on('click', function(){

    if($('#ttsaudio_option_text_to_speech').val() == '') {
      $('#ttsaudio_option_text_to_speech').focus();
      alert('Please enter text!');
      return false;
    }

    $(this).after('<span class="spinner"></span>');
    var spinner = $(this).siblings('.spinner');
    var ajax_result = $(this).siblings('.ajax_result');
    var audio_file = $(this).siblings('.audio_file');

    spinner.css('visibility', 'visible');
    ajax_result.empty();

    var data = {
        action: 'ttsaudio_create_audio',
        security: $(this).data('security'),
        post_id: $('#post_ID').val(),
        voice: $('#ttsaudio_option_voice').val(),
        text: $('#ttsaudio_option_text_to_speech').val()
    };

    $.post(ajaxurl, data, function(response) {
        $('#ttsaudio_option_mp3').val(response);
        spinner.remove();
        ajax_result.html('<font color="green"><strong>Done!</strong></font>');
        audio_file.text(response);
    });

  });


});
