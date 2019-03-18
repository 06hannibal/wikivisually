<?php

namespace Drupal\search_page\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class OpenFileXmlController
 * @package Drupal\search_page\Controller
 */
class SearchController extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function search() {
      $title = \Drupal::request()->attributes->get('title');
      $url_wiki = "https://en.wikipedia.org/w/api.php?action=query&format=json&generator=search&gsrsearch=";
      //displaying an entire article
      $mh = curl_multi_init();
      $chs = [];
      $chs['ID0001'] = curl_init("{$url_wiki}{$title}&gsrnamespace=0&gsrlimit=10&prop=extracts|pageimages&exchars=200&exlimit=max&explaintext=true&exintro=true&piprop=thumbnail&pilimit=max&pithumbsize=200");
      // $chs[] = ...
      foreach ($chs as $ch) {
          curl_setopt_array($ch, [
              CURLOPT_RETURNTRANSFER => true,  // Return requested content as string
              CURLOPT_HEADER => false,         // Don't save returned headers to result
              CURLOPT_CONNECTTIMEOUT => 3,    // Max seconds wait for connect
              CURLOPT_TIMEOUT => 5,           // Max seconds on all of request
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
      $search_json = json_decode($responses['ID0001'], true);
      foreach ($search_json['query']['pages'] as $value) {
          $url_title = str_replace(' ', '_', $value['title']);
          $search_page[] = [
              'title' => $value['title'],
              'url_title' => $url_title,
              'description' => $value['extract'],
              'thumbnail' => $value['thumbnail']['source']
          ];
      }
      if($search_page == null){
          $search_page = "http://unbxd.com/blog/wp-content/uploads/2014/02/No-results-found.jpg";
      }
    $build[] = [
      '#theme' => 'search_page',
      '#search_page' => $search_page,
    ];
    $build['#attached']['library'][] = 'search_page/search_page';

    return $build;

  }
    /**
     * {@inheritdoc}
     */
    public function getCacheMaxAge() {
        return 0;
    }
}
