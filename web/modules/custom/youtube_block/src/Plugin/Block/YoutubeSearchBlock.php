<?php

namespace Drupal\youtube_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Defines a Youtube block block type.
 *
 * @Block(
 *   id = "youtube_search_block",
 *   admin_label = @Translation("Youtube Search Block"),
 *   category = @Translation("Youtube"),
 * )
 */
class YoutubeSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $config = $this->getConfiguration();

    $form['youtube_search'] = [
      '#type' => 'fieldset',
      '#title' => t('Youtube search settings'),
    ];

    $form['youtube_search']['youtube_api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Youtube Google API Key'),
      '#size' => 40,
      '#default_value' => $config['youtube_api_key'],
      '#required' => TRUE,
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    foreach (['youtube_search'] as $fieldset) {
      $fieldset_values = $form_state->getValue($fieldset);
      foreach ($fieldset_values as $key => $value) {
        $this->setConfigurationValue($key, $value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $baseUrl = 'https://www.googleapis.com/youtube/v3/';
    // https://developers.google.com/youtube/v3/getting-started
    $apiKey = $config['youtube_api_key'];
    $video_limit = 6;

    $title_request = \Drupal::request()->getRequestUri();
    $title_search = substr($title_request, 6);
    //url video search
    $url_search = "{$baseUrl}search?part=id&q={$title_search}&type=video&key={$apiKey}&maxResults={$video_limit}";
    $json_search = json_decode(file_get_contents($url_search), true);

    foreach($json_search['items'] as $value) {
      //snippet information video youtube
      $video_id = $value['id']['videoId'];
      $url_snippet = "{$baseUrl}videos?part=snippet&id={$video_id}&key={$apiKey}";
      $json_snippet = json_decode(file_get_contents($url_snippet), true);
      //youtube video statistics
      $url_statistic = "{$baseUrl}videos?part=statistics&id={$video_id}&key={$apiKey}";
      $json_statistic = json_decode(file_get_contents($url_statistic), true);
      //time video youtube
      $url_duration = "{$baseUrl}videos?id={$video_id}&part=contentDetails&key={$apiKey}";
      $json_duration = json_decode(file_get_contents($url_duration), true);

      foreach ($json_snippet['items'] as $value_snippet) {
        $videos_img = $value_snippet['snippet']['thumbnails']['high']['url'];
        $title = $value_snippet['snippet']['title'];

        foreach ($json_duration['items'] as $value_duration){
          $video_time = $value_duration['contentDetails']['duration'];
          $time = substr($video_time, 2);
          $duration_init = explode("M", $time);
          $time_duration = $duration_init[0]."M";

          foreach ($json_statistic['items'] as $value_statistic) {
            $viewCount = $value_statistic['statistics']['viewCount'];
            //add number for views

            if ($viewCount > 1000000) {
              $english_format_number = number_format($viewCount);
              $viewCount = round($english_format_number).'m';
            } elseif ($viewCount > 1000) {
              $english_format_number = number_format($viewCount);
              $viewCount = round($english_format_number).'k';
            }
            $likeCount = $value_statistic['statistics']['likeCount'];
            //add number for like

            if ($likeCount > 1000000) {
              $english_format_number = number_format($likeCount);
              $likeCount = round($english_format_number).'m';
            } elseif ($likeCount > 1000) {
              $english_format_number = number_format($likeCount);
              $likeCount = round($english_format_number).'k';
            }

            $youtube_search[] = [
              'title' => $title,
              'viewCount' => $viewCount,
              'likeCount' => $likeCount,
              'video_id' => $video_id,
              'videos_img' => $videos_img,
              'duration' => $time_duration,
            ];
          }
        }
      }
    }
    $build[] = [
      '#theme' => 'youtube_search',
      '#youtube_search' => $youtube_search,
    ];

    $build['#attached']['library'][] = 'youtube_block/youtube_block';

    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
