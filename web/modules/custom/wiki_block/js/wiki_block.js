/**
 * @file
 * Embed YouTube videos on Click.
 */
(function($, Drupal) {
  var count = 1;

  function wikiblock_popap(src) {
    z_index = ++count;
    string = src.split("/");
    if (string[4] == "en" && string[5] == "thumb") {
      title = src.split("/")[8].split(".")[0].split("%")[0];
      jQuery('.'+title).attr('src','https://upload.wikimedia.org/wikipedia/en/thumb/'+string[6]+'/'+string[7]+'/'+string[8]+'/600px-'+string[8]);
    } else if (string[5] == "thumb") {
      title = src.split("/")[8].split(".")[0].split("%")[0];
      jQuery('.'+title).attr('src','https://upload.wikimedia.org/wikipedia/commons/thumb/'+string[6]+'/'+string[7]+'/'+string[8]+'/600px-'+string[8]);
    } else {
      title = src.split("/")[7].split(".")[0].split("%")[0];
      jQuery('.'+title).attr('src','https://upload.wikimedia.org/wikipedia/commons/'+string[5]+'/'+string[6]+'/'+string[7]);
    }
    $('.'+title).css({
      "position": "fixed",
      "width": "30%",
      "z-index": z_index,
      "border": "2em solid #1565c0",
      "background": "#fff"
    });
    $('.link'+title).css({
      "z-index": z_index,
    });
    $('a.link'+title).css({
      "text-decoration": "none"
    });
    $('.all'+title).css({
      "z-index": z_index,
    });
    $('a.all'+title).css({
      "text-decoration": "none"
    });
    $('.'+title).once().click(function(e) {
      e.preventDefault();
      return false;
    });
    $('.link'+title).once().click(function() {
      $(this).parent().remove();
    });
    $('.all'+title).once().click(function() {
      $('.img_close').remove();
      $('div.wiki_class_popap').remove();
      $('div.popap-wiki').remove();
    });
  }
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
                    url: url_wiki+"query&prop=imageinfo&iiprop=url&generator=images&titles="+title_wiki_url+"&format=json&iiurlwidth=200&iiformat=jpg&gimlimit=4",
                    dataType: "jsonp",
                    async: false,
                    data: {name: name},
                    success: function (data) {
                      $('div.wiki_img_block').append("<div class='title-wiki-block'><a class='title-wiki-block-popap' href='/wiki/"+title_wiki_url+"'>"+title+"</a> <a href='https://www.youtube.com/results?search_query="+title_wiki_url+"' target='_blank'>[more videos]</a><hr class='wiki_img_hr'/><div class='description-wiki-block'>"+description+"</div></div>");
                      if(data.query != null) {
                        $.each (data.query.pages, function (index, value){
                          var url_image = value.imageinfo[0].thumburl;
                          if (~url_image.indexOf("jpg") || ~url_image.indexOf("JPG")){
                            var string = url_image.split("/")[8].split(".")[0].replace(/%/g," ").replace(/_/g," ");
                            var title = string;

                            $('div.wiki_img_block').append("<div class='url_img_wiki'><img src='"+url_image+"'><p class='text_title_wiki'>"+title+"</p></div>");
                          }
                        });
                      }
                    }
                  });

                }
              }
            }
          }
          $(function () {
            $(".url_img_wiki img").once().click(function(e){
              $('a.image img.img_close').remove();
              $('a.image a.img_close').remove();
              string = $( this ).attr('src').split("/");
              e.preventDefault();
              if (string[5] == "thumb") {
                img_link = $( this );
                title = img_link.attr('src').split("/")[8].split(".")[0].split("%")[0];
              }  else if (string[4] == "en") {
                img_link = $( this );
                title = img_link.attr('src').split("/")[8].split(".")[0].split("%")[0];
              } else {
                img_link = $( this );
                title = img_link.attr('src').split("/")[7].split(".")[0].split("%")[0];
              }
              $( this ).parent().append( $("<div class='wiki_class_popap'><img class='img_close class_popap "+title+"' alt='Wiki Img Frame' src=''><a class='img_close class_link link"+title+"' onclick='return false'>X</a><a class='img_close class_all all"+title+"' onclick='return false'>close all</a></div>"));
              // $( this ).parent().append( $(""));
              // $( this ).parent().append( $(""));
              wikiblock_popap(jQuery(this).attr('src'));
              return false;
            });
            $("div.wiki_img_block div.title-wiki-block a.title-wiki-block-popap").once().hover(function(){
              wiki_link = $( this );
              url_title_wiki = wiki_link.attr('href');
              title_wiki = url_title_wiki.substr(6);
              if (screen.width > 1199) {
                $( this ).append( $("<div class='popap-wiki style-popap-wiki'></div>"));
              }
              jQuery.ajax({
                url: "https://en.wikipedia.org/w/api.php?format=json&action=parse&page="+ title_wiki,
                dataType: "jsonp",
                success: function (rows) {
                  page_wiki = rows;
                  $(page_wiki).each(function() {
                    $('div.popap-wiki').append().html(page_wiki.parse.text['*']);
                  });
                }
              });
            },function() {
              $( this ).find("div.popap-wiki").remove();
            });
          });
        });
      }
    }
  }

}(jQuery, Drupal));
