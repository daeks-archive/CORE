(function ($) {
    
    var sidebar = (function () {
      var init = function () {
        $('[data-toggle=collapse]').click(function(){
          $(this).find("b").toggleClass("glyphicon-chevron-right glyphicon-chevron-down");
        });
      };
            
      return {
        init: init
      };
    })();

    $.extend(true, window, {
      core: {
        sidebar: sidebar
      }
    });

    $(function () {
        core.sidebar.init();
    });

}(jQuery));
