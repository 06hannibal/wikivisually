/**
 * @file
 * Embed YouTube videos on Click.
 */
(function($, Drupal) {
    function youtubesearch_setvideo(url_img_link) {
                   substr_video_youtube = url_img_link.substr(23);
                   split_video_youtube = substr_video_youtube.split('/');
                   id_video_youtube = split_video_youtube[0];
        jQuery('.youtubesearch-frame').attr('src','https://www.youtube.com/embed/'+id_video_youtube);
  }
  Drupal.behaviors.youtube_block = {
      attach:function() {
        $(function () {
          $("div.left-stat img").once().click(function(){
            $(this).parent().next().next().toggle();
            $(this).parent().next().toggle();
            document.body.style.overflow = "hidden";
            document.body.style.position = "fixed";
            youtubesearch_setvideo(jQuery(this).attr('src'));
            return false;
          });
        });
        $("a.youtubesearch-close").once().click(function() {
          $('.youtubesearch-frame').attr('src','');
          $(this).next().hide();
          $(this).hide();
          document.body.style.overflow = "visible";
          document.body.style.position = "unset";
        });
      }
    }
}(jQuery, Drupal));
