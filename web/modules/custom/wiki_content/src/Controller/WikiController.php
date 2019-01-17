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
    $wiki_url = "https://en.wikipedia.org/w/api.php?format=json&action=parse&page={$title}";
    $wiki_json = json_decode(file_get_contents($wiki_url), true);
    $wiki_content = $wiki_json['parse']['text']['*'];

    $build[] = [
      '#theme' => 'wiki_content',
      '#article' => $wiki_content,
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
