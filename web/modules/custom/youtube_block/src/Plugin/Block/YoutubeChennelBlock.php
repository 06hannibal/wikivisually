<?php

namespace Drupal\youtube_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\vendor\guzzlehttp\guzzle\src\Exception;

/**
* Defines a Youtube block block type.
 *
 * @Block(
 *   id = "youtube_channel_block",
 *   admin_label = @Translation("Youtube Channel Block"),
 *   category = @Translation("Youtube"),
 * )
 */
class YoutubeChennelBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $config = $this->getConfiguration();

    $form['youtube_channel'] = [
      '#type' => 'fieldset',
      '#title' => t('Youtube channel settings'),
    ];

    $form['youtube_channel']['youtube_api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Youtube Google API Key'),
      '#size' => 40,
      '#default_value' => $config['youtube_api_key'],
      '#required' => TRUE,
    ];

    $form['youtube_channel']['youtube_id'] = [
      '#type' => 'textfield',
      '#title' => t('Youtube Channel ID'),
      '#size' => 40,
      '#default_value' => $config['youtube_id'],
      '#required' => TRUE,
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    foreach (['youtube_channel'] as $fieldset) {
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
    // If you don't know the channel ID see below
    $channelId = $config['youtube_id'];
    $video_limit = 10;
    //playlist snippet information youtube
    $url = "{$baseUrl}playlistItems?part=snippet&playlistId={$channelId}&key={$apiKey}&maxResults={$video_limit}";
    $json = json_decode(file_get_contents($url), true);

    foreach($json['items'] as $value) {
      $youtube_id = $value['snippet']['resourceId']['videoId'];
      $videos_img = $value['snippet']['thumbnails']['high']['url'];
      //youtube video statistics
      $url_statistic = "{$baseUrl}videos?part=statistics&id={$youtube_id}&key={$apiKey}";
      $json_statistic = json_decode(file_get_contents($url_statistic), true);
      //time video youtube
      $url_duration = "{$baseUrl}videos?id={$youtube_id}&part=contentDetails&key={$apiKey}";
      $json_duration = json_decode(file_get_contents($url_duration), true);

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

          $youtube_informatin[] = [
            'title' => $value['snippet']['title'],
            'viewCount' => $viewCount,
            'likeCount' => $likeCount,
            'youtube_id' => $youtube_id,
            'videos_img' => $videos_img,
            'duration' => $time_duration,
          ];
        }
      }
    }
    $build[] = [
      '#theme' => 'youtube_block',
      '#youtube_informatin' => $youtube_informatin,
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
