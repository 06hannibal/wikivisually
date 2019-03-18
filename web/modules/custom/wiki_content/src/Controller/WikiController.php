<?php

namespace Drupal\wiki_content\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class OpenFileXmlController
 * @package Drupal\wiki_content\Controller
 */
class WikiController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function wiki() {
    $title = \Drupal::request()->attributes->get('title');
    $url_wiki = "https://en.wikipedia.org/w/api.php?";
    //displaying an entire article
    $mh = curl_multi_init();
    $chs = [];
    $chs['ID0001'] = curl_init("{$url_wiki}format=json&action=parse&page={$title}");
    $chs['ID0002'] = curl_init("{$url_wiki}action=query&list=search&srsearch={$title}&format=json&srprop=pageid&srqiprofile=wsum_inclinks_pv");
    // $chs[] = ...
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
    // Close the handles
    foreach ($chs as $ch) {
      curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    $responses = [];
    foreach ($chs as $id => $ch) {
      $responses[$id] = curl_multi_getcontent($ch);
      curl_close($ch);
    }
    $wiki_json = json_decode($responses['ID0001'], true);
    //search related articles
    $json_pageid = json_decode($responses['ID0002'], true);
    $wiki_content = $wiki_json['parse']['text']['*'];

    $n = 0;
    foreach($json_pageid['query']['search'] as $pageid_value) {
      $title_page = $pageid_value['title'];
      $str_replace_title = str_replace(' ', '_', $title_page);

      if($title !== $str_replace_title) {
        $related_articles = $str_replace_title;
        if(!is_null($related_articles)) {
          $url_description_article = "{$url_wiki}action=query&prop=extracts&format=json&exintro=&titles={$related_articles}";
          $json_description_article = json_decode(file_get_contents($url_description_article), true);
          foreach ($json_description_article['query']['pages'] as $key => $text_article) {
            $text_article_strip_tags = strip_tags($text_article['extract']);
            $url_related_topic = str_replace(' ', '_', $text_article['title']);

            $related_topic[] = [
              'n' => ++$n.'.',
              'url_related_topic' => $url_related_topic,
              "headline_related_topic" => $text_article['title'],
              "description_related_topic" => $text_article_strip_tags,
            ];
          }
        }
      }
    }
    $build[] = [
      '#theme' => 'wiki_content',
      '#article' => $wiki_content,
      '#related_topic' => $related_topic,
    ];
    $build['#attached']['library'][] = 'wiki_content/wiki_content';

    return $build;

  }
  /**
   * {@inheritdoc}
   */
  public function wikititle() {
    $title = \Drupal::request()->attributes->get('title');
    $page_title = str_replace('_', ' ', $title);
    return $page_title;
  }
    /**
     * {@inheritdoc}
     */
    public function getCacheMaxAge() {
        return 0;
    }
}
