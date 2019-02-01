/**
 * @file
 * Embed YouTube videos on Click.
 */
// (function($, Drupal) {
//     Drupal.behaviors.wiki_content = {
//         attach:function() {
//             $(function () {
//                 $("div.mw-parser-output a").once().hover(function(){
//                     wiki_link = $( this );
//                     url_title_wiki = wiki_link.attr('href');
//                     title_wiki = url_title_wiki.substr(6);
//                     $( this ).append( $("<div class='popap-wiki'>"+
//                         jQuery.ajax({
//                             url: "https://en.wikipedia.org/w/api.php?format=json&action=parse&page="+ title_wiki,
//                             dataType: "jsonp",
//                             success: function (rows) {
//                                 page_wiki = rows;
//                                 $(page_wiki).each(function() {
//                                     $('div.popap-wiki').append(page_wiki.parse.text['*']);
//                                 });
//                             }
//                         })+"</div>"));
//                     },function() {
//                     $( this ).find("div.popap-wiki").remove();
//                 })
//             });
//         }
//     }
// }(jQuery, Drupal));

