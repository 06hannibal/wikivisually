/**
 * @file
 * Embed YouTube videos on Click.
 */
(function($, Drupal) {
  Drupal.behaviors.search_page = {
    attach: function attach(context) {
      if (context === document){
          $('#wiki-search-article').submit(function (e) {
            e.preventDefault();
            var title_replace = $('input').val();
            var title_article = title_replace.replace(/ /g,"_");
            var URL = 'search/' + title_article;
            document.location.href="/"+URL;
          });
      }
    }
  }
}(jQuery, Drupal));
