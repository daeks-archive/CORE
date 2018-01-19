(function ($) {
    
    var modal = (function () {
      var init = function () {
        $('.modal').on('show.bs.modal', global.modal.center);
        $('.modal').on('loaded.bs.modal', global.modal.center);
        $('.modal').on('hidden.bs.modal', function (e) {
          $('.modal').removeData('bs.modal');
          $('#modal-content').html('<br>&nbsp;&nbsp;<i class="fa fa-spinner fa-spin"></i> Loading...<br><br>');
        });

        $(window).on('resize', function () {
          $('.modal:visible').each(global.modal.center);
        });

        $('.modal').on('success.form.fv', function (event) {
          $('button[data-query="modal-data"]').html('<i id="loading" class="fa fa-spinner fa-spin"></i> ' + $('button[data-query="modal-data"]').html());
          $('button[data-query="modal-data"]').prop('disabled', true);
          if ($('#modal-data').length > 0) {
            var $form = $('#modal-data');
            var $target = $($form.attr('data-target'));

            $.ajax({
              type: $form.attr('method'),
              url: $form.attr('action'),
              data: $form.serialize(),

              success: function (data, status) {
                try {
                  var obj = $.parseJSON(data);
                  if (obj.status == 200) {
                    if (obj.event.length > 0) {
                      $('.modal').modal('hide');
                      if (obj.data.length > 0) {
                        global.message.toast('success', false, obj.data);
                      }
                      eval(obj.event);
                    } else {
                      var data = $('<textarea/>').html(obj.data).val();
                      $target.html(data);
                      global.modal.center();
                    }
                  } else if (obj.status == 500) {
                    $('.modal').modal('hide');
                    global.message.toast('danger', false, obj.data);
                  } else {
                    $('.modal').modal('hide');
                    global.message.toast('danger', true, obj.data);
                  }
                } catch (e) {
                  $('.modal').modal('hide');
                  global.message.infobox('danger', 0, e.message + '<br>' + data);
                }
              }
            });
            $(this).prop('disabled', true);
            event.preventDefault();
          }
        });
      };
      
      var center = function () {
        $(this).css('display', 'block');
        var $dialog = $(this).find('.modal-dialog');
        var offset = ($(window).height() - $dialog.height()) / 2;
        var bottomMargin = $dialog.css('marginBottom');
        bottomMargin = parseInt(bottomMargin);
        if (offset < bottomMargin) {
          offset = bottomMargin;
        }
        $dialog.css("margin-top", offset);
        global.validator.reinit();
        global.form.init();
        $('button[data-query="modal-data"]').on('click', function (event) {
          $('#modal-data').submit();
          event.preventDefault();
          return false;
        });
      };
      
      return {
        init: init,
        center: center
      };
    })();

    $.extend(true, window, {
      global: {
        modal: modal
      }
    });

    $(function () {
        global.modal.init();
    });

}(jQuery));
