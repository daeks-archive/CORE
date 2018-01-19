(function ($) {
    
    var table = (function () {
      var init = function () {
      };
      
      var refresh = function (params) {
        if (params === undefined) {
          $('#table').bootstrapTable("refresh");
        } else {
          var loadurl = $('#table').attr('data-query');
          if (loadurl.indexOf('?') > -1) {
            loadurl = loadurl + '&q=' + params.data.search + '&s=' + params.data.sort + '&o=' + params.data.order + '&l=' + params.data.limit + '&p=' + params.data.offset;
          } else {
            loadurl = loadurl + '?q=' + params.data.search + '&s=' + params.data.sort + '&o=' + params.data.order + '&l=' + params.data.limit + '&p=' + params.data.offset;
          }
              
          $.get(loadurl, function (data) {
            try {
              var obj = $.parseJSON(data);
              if (obj.status == 200) {
                params.success({total: obj.total, rows: $.parseJSON(obj.data)});
              } else if (obj.status == 401) {
                global.message.infobox('danger', false, obj.data);
                params.error({status: 401});
              } else if (obj.status == 500) {
                global.message.toast('danger', false, obj.data);
              } else {
                global.message.infobox('danger', 0, obj.data);
                params.error({status: obj.status});
              }
            } catch (e) {
              global.message.infobox('danger', 0, e.message + data);
              params.error({status: 500});
            }
          });
        }
      };
      
      return {
        init: init,
        refresh: refresh
      };
    })();

    $.extend(true, window, {
      global: {
        table: table
      }
    });

    $(function () {
        global.table.init();
    });

}(jQuery));