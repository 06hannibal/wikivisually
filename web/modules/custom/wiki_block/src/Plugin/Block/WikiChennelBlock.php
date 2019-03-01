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
    $build[] = [
      '#theme' => 'wiki_block',
    ];

    $build['#attached']['library'][] = 'wiki_block/wiki_block';

    return $build;
  }
}
