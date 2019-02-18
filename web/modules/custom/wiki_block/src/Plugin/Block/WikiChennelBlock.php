<?php

namespace Drupal\wiki_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Defines a Wiki block block type.
 *
 * @Block(
 *   id = "wiki_block_block",
 *   admin_label = @Translation("Wiki Channel Block"),
 *   category = @Translation("Wikivisually"),
 * )
 */
class WikiChennelBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $url_wiki = "https://en.wikipedia.org/w/api.php?action=";
    $title_page_wiki = \Drupal::request()->attributes->get('title');
    //article search
    $url_pageid = "{$url_wiki}query&list=search&srsearch={$title_page_wiki}&format=json&srprop=pageid&srqiprofile=wsum_inclinks_pv";
    $json_pageid = json_decode(file_get_contents($url_pageid), true);
    foreach($json_pageid['query']['search'] as $pageid_value) {
      $title_page = $pageid_value['title'];
      $str_replace_title = str_replace(' ', '_', $title_page);
      if($title_page_wiki !== $str_replace_title) {
        $title_article = $str_replace_title;
        //output images from an article

        $mh = curl_multi_init();
        $chs = [];
        $chs['ID0001'] = curl_init("{$url_wiki}query&titles={$title_article}&format=json&prop=images");
        $chs['ID0002'] = curl_init("{$url_wiki}opensearch&search={$title_article}&limit=1&format=json");
        foreach ($chs as $ch) {
          curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,  // Return requested content as string
            CURLOPT_HEADER => false,         // Don't save returned headers to result
            CURLOPT_CONNECTTIMEOUT => 5,    // Max seconds wait for connect
            CURLOPT_TIMEOUT => 10,           // Max seconds on all of request
            CURLOPT_USERAGENT => 'Robot YetAnotherRobo 1.0',
          ]);
          curl_multi_add_handle($mh, $ch);
        }
        $running = null;
        do {
          curl_multi_exec($mh, $running);
        } while ($running);
        foreach ($chs as $ch) {
          curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        $responses = [];
        foreach ($chs as $id => $ch) {
          $responses[$id] = curl_multi_getcontent($ch);
          curl_close($ch);
        }
        unset($chs); // Finita, no more need any curls :-)
        $json_image_article = json_decode($responses['ID0001'], true);

        //output description to article
        $json_description_title = json_decode($responses['ID0002'], true);

        foreach ($json_description_title as $key => $value_description) {
          $description = [$key => $value_description];
          if (!is_null($description[2])) {
            $description_title = mb_strimwidth($description[2][0], 0, 100, "...");
            foreach ($json_image_article['query']['pages'] as $pages_value) {
              foreach($pages_value['images'] as $image) {
                if (strtolower(pathinfo($image['title'], PATHINFO_EXTENSION)) !== 'svg') {
                  if (!is_null($image['title'])) {
                    $str_replace_img = str_replace(' ', '_', $image['title']);
                    //output image link
                    $url_info_image = "{$url_wiki}query&titles={$str_replace_img}&prop=imageinfo&iiprop=url&format=json&iiurlwidth=150";
                    $title_article_wiki = str_replace('_', ' ', $title_article);
                    $json_image_article = json_decode(file_get_contents($url_info_image), true);
                    foreach ($json_image_article['query']['pages']['-1']['imageinfo'] as $url_img) {
                      $img_explode_start = explode('/', $url_img['thumburl'], 8);
                      $img_explode_finish = explode('.', $img_explode_start[7]);
                      $title_img_str_replace = str_replace('_', ' ', $img_explode_finish[0]);
                      $title_img = str_replace('%', '', $title_img_str_replace);
                      $wiki_information[$title_article_wiki][] = [
                        'url_title' => $str_replace_title,
                        'img' => $url_img['thumburl'],
                        'title_img' => $title_img,
                        "description" => $description_title,
                      ];
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    $wiki_inf["content"] = $wiki_information;
    $build[] = [
      '#theme' => 'wiki_block',
      '#wiki_information' => $wiki_inf,
    ];
    $build['#attached']['library'][] = 'wiki_block/wiki_block';
    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
