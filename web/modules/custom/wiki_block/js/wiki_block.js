/**
 * @file
 * Embed YouTube videos on Click.
 */
(function($, Drupal) {
  Drupal.behaviors.wiki_block = {
    attach: function attach(context) {
      if (context === document) {
          var url_wiki = "https://en.wikipedia.org/w/api.php?action=";
          var title_page_wiki = window.location.href.split("/")[4];
          $.ajaxSetup({async: false});
          jQuery.ajax({
            url: url_wiki+"query&list=search&srsearch="+title_page_wiki+"&format=json&srprop=pageid&srqiprofile=wsum_inclinks_pv",
            dataType: "jsonp",
            data: {name: name},
            success: function (data) {
              var rows_search = data.query.search;
              $(rows_search).each(function(index, value) {
                var title_article = value.title.replace(/ /g,"_");

                if(title_article != title_page_wiki) {
                  jQuery.ajax({
                    url: url_wiki+"opensearch&search="+title_article+"&limit=1&format=json",
                    dataType: "jsonp",
                    data: {name: name},
                    success: function (data) {}
                  });
                }
              });
            }
          });
          $.ajaxSetup({async: true});
        $(document).ajaxComplete(function(event, XMLHttpRequest, ajaxOptions) {

          if(XMLHttpRequest.responseJSON[1] != null) {
            if(XMLHttpRequest.responseJSON[1][0] != null) {
              if(XMLHttpRequest.responseJSON[2] != null) {
                if(XMLHttpRequest.responseJSON[2][0] != null) {
                  var title = XMLHttpRequest.responseJSON[1][0];
                  var description = XMLHttpRequest.responseJSON[2][0];
                  var title_wiki_url = XMLHttpRequest.responseJSON[1][0].replace(/ /g,"_");

                  jQuery.ajax({
                    url: url_wiki+"query&prop=imageinfo&iiprop=url&generator=images&titles="+title_wiki_url+"&format=json&iiurlwidth=150&iiformat=jpg&gimlimit=4",
                    dataType: "jsonp",
                    async: false,
                    data: {name: name},
                    success: function (data) {
                      $('div.wiki_img_block').append("<div class='info-wrapper'><div class='title-wiki-block'><a class='title-wiki-block-popap' href='/wiki/"+title_wiki_url+"'>"+title+"</a> <a href='https://www.youtube.com/results?search_query="+title_wiki_url+"' target='_blank'>[more videos]</a></div><div class='description-wiki-block'>"+description+"</div></div>");
                      if(data.query != null) {
                        $.each (data.query.pages, function (index, value){
                          var url_image = value.imageinfo[0].thumburl;
                          if (~url_image.indexOf("jpg") || ~url_image.indexOf("JPG")){
                            $('div.wiki_img_block').append("<div class='url_img_wiki'><img src='"+url_image+"'></div>");
                          }
                        });
                      }
                    }
                  });
                }
              }
            }
          }
        });
      }
    }
  }
}(jQuery, Drupal));
