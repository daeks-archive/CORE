(function ($) {
    
    var core = (function () {

      var init = function () {
        var scripts= document.getElementsByTagName('script'),
        path = scripts[scripts.length-1].src.split('?')[0],
        curpath = path.split('/').slice(0, -1).join('/')+'/',
        parpath = curpath.split('/').slice(0, -2).join('/')+'/';
      
        $.get(parpath+'setup/controller.php?action=init', function (data) {
          try {
            var obj = $.parseJSON(data);
            if (obj.status == 200) {
              if (obj.data > 0) {
                $('.modal-content').load(parpath+'setup/dialog.php?action=setup',function (result) {
                  $('.modal').modal({
                    show:true,
                    backdrop: 'static',
                    keyboard: false
                  });
                  $('.modal').css('display', 'block');
                  var $dialog = $('.modal').find('.modal-dialog');
                  var offset = ($(window).height() - $dialog.height()) / 2;
                  var bottomMargin = $dialog.css('marginBottom');
                  bottomMargin = parseInt(bottomMargin);
                  if (offset < bottomMargin) {
                    offset = bottomMargin;
                  }
                  $dialog.css('margin-top', offset);
                });
              }
            } else if (obj.status == 500) {
              core.toast('danger', false, obj.data);
            } else {
              core.toast('danger', true, obj.data);
            }
          } catch (e) {
            core.infobox('danger', 0, e.message + data);
          }
        });
      };
      
      var infobox = function (type, time, value) {
        $('#infobox').html('<div class="alert alert-' + type + '" tabindex="-1"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><span>' + value.replace('Unexpected token <', '').trim() + '</span></div>');
        $('#infobox').show();
        if (time > 0) {
          setTimeout(function () {
            $("#infobox").hide();
          }, time);
        }
      };
      
      var toast = function (type, sticky, value) {
        $().toastmessage('showToast', {
          text: value,
          sticky: sticky,
          position: 'bottom-right',
          type: type
        });
      };
      
      var reload = function () {
        location.reload();
      };
      
      return {
        init: init,
        infobox: infobox,
        toast: toast,
        reload: reload
      };
    })();

    $.extend(true, window, {
      core: core
    });

    $(function () {
        core.init();
    });

}(jQuery));
