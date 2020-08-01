(function($){

  "use strict";

  $('#ttsaudio_form .tbody-more').hide();

  $(document).on('change', '#ttsaudio_status', function (e) {
    $(this).after('<span class="spinner"></span>');
    var spinner = $(this).siblings('.spinner');
    spinner.css('visibility', 'visible');

    var status = $(this).val();
    // var other_tr = $(this).closest('tr').siblings('tr');
    //
    // if(status  == 'enable') other_tr.show();
    // else other_tr.hide();
    var data = {
        action: 'ajax_add_fields_meta_boxes',
        security: $('#ttsaudio_status_security').val(),
    };
    $.post(ajaxurl, data, function(response) {
      if(status  == 'enable') $('table#ttsaudio_form').append(response);
      else $('table#ttsaudio_form .tbody-more').hide();
      spinner.remove();
    });
  });

  //Calculate characters length for textarea
  $(document).on('change keyup paste', '#ttsaudio_option_text_to_speech', function (e) {
    var currentVal = $(this).val().length + ' characters';
    $(this).next('p').text(currentVal);
  });

  //Create MP3 file
  $(document).on('click', '#ttsaudio_create_mp3', function(){

    if($('#ttsaudio_option_text_to_speech').val() == '') {
      $('#ttsaudio_option_text_to_speech').focus();
      alert('Please enter text!');
      return false;
    }

    $(this).after('<span class="spinner"></span>');
    var spinner = $(this).siblings('.spinner');
    var ajax_result = $(this).siblings('.ajax_result');

    spinner.css('visibility', 'visible');
    ajax_result.empty();

    var data = {
        action: 'ehi_wp_custom_stuff',
        security: $('[name=ttsaudio_ajax_security]').val(),
        voice: $('#ttsaudio_option_voice').val(),
        text: $('#ttsaudio_option_text_to_speech').val()
    };

    $.post(ajaxurl, data, function(response) {
        $('#ttsaudio_option_mp3').val(response);
        spinner.remove();
        ajax_result.html('<font color="green"><strong>Done!</strong></font>');
    });

  });

  // $(document).on('click', '#ttsaudio_create_mp3', function(e){
  //
  //   $.ajax({
  //       type: "POST",
  //       data: {
  //           action: 'ehi_wp_custom_stuff', // This is the action in your server-side code (PHP) that will be triggered
  //           ehi_term_for_ajax_live_search: ehi_search_term
  //       },
  //       url: ehi_wp_live_search_ajax_object.ajax_url,
  //       success: function(ehiresult)
  //       {
  //           var objresult = document.getElementById('ehi_ajaxlivesearchresults');
  //           objresult.innerHTML = ehiresult;
  //       }
  //   });
  //
  // });

}(jQuery));
