/**
 * @file
 * Embed YouTube videos on Click.
 */
(function($, Drupal) {
    var count = 1;
    function wikicontent_setimg(src) {
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
        "height": "auto",
        "right": "10%",
        "top": "20%",
        "z-index": z_index,
        "border": "2em solid rgb(52, 46, 56)",
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
        $(this).prev().remove();
        $(this).next().remove();
        $(this).remove();
    });
    $('.all'+title).once().click(function() {
        $('.img_close').remove();
    });
    }
    Drupal.behaviors.wiki_content = {
        attach:function() {
            $(function () {
                $("div.mw-parser-output a").once().hover(function(){wikicontent_setimg(jQuery(this).attr('src'));
                    wiki_link = $( this );
                    url_title_wiki = wiki_link.attr('href');
                    title_wiki = url_title_wiki.substr(6);
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
                    $( this ).append( $("<div class='popap-wiki'></div>"));
                    },function() {
                    $( this ).find("div.popap-wiki").remove();
                });

                $("a.image img").once().click(function(e){
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
                    $( this ).parent().append( $("<img class='img_close "+title+"' alt='Wiki Img Frame' src=''>"));
                    $( this ).parent().append( $("<a class='img_close class_link link"+title+"' onclick='return false'>X</a>"));
                    $( this ).parent().append( $("<a class='img_close class_all all"+title+"' onclick='return false'>close all</a>"));
                    wikicontent_setimg(jQuery(this).attr('src'));
                    return false;
                });
            });

        }
    }
}(jQuery, Drupal));
