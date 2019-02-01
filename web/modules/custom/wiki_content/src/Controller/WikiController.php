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
    $wiki_url = "{$url_wiki}format=json&action=parse&page={$title}";
    $wiki_json = json_decode(file_get_contents($wiki_url), true);
    $wiki_content = $wiki_json['parse']['text']['*'];
    //search related articles
    $url_pageid = "{$url_wiki}action=query&list=search&srsearch={$title}&format=json&srprop=pageid&srqiprofile=wsum_inclinks_pv";
    $json_pageid = json_decode(file_get_contents($url_pageid), true);
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
}
