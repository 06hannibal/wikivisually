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

    $mh = curl_multi_init();
    $chs = [];
    $chs['ID0001'] = curl_init("{$baseUrl}search?part=id&q={$title_search}&type=video&regionCode=US&key={$apiKey}&maxResults={$video_limit}");
    foreach ($chs as $ch) {
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,  // Return requested content as string
        CURLOPT_HEADER => false,         // Don't save returned headers to result
        CURLOPT_CONNECTTIMEOUT => 1,    // Max seconds wait for connect
        CURLOPT_TIMEOUT => 2,           // Max seconds on all of request
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
    $json_search = json_decode($responses['ID0001'], true);

    //url video search
    foreach($json_search['items'] as $value) {
      //snippet information video youtube
      $video_id = $value['id']['videoId'];

      $mh = curl_multi_init();
      $chs = [];
      $chs['ID0002'] = curl_init("{$baseUrl}videos?part=snippet&id={$video_id}&key={$apiKey}");
      $chs['ID0003'] = curl_init("{$baseUrl}videos?part=statistics&id={$video_id}&key={$apiKey}");
      $chs['ID0004'] = curl_init("{$baseUrl}videos?id={$video_id}&part=contentDetails&key={$apiKey}");
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
      $json_snippet = json_decode($responses['ID0002'], true);
      //youtube video statistics
      $json_statistic = json_decode($responses['ID0003'], true);
      //time video youtube
      $json_duration = json_decode($responses['ID0004'], true);

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
