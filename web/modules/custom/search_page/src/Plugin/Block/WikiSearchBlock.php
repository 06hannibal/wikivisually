<?php

namespace Drupal\search_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Defines a Wiki block block type.
 *
 * @Block(
 *   id = "wiki_search_block",
 *   admin_label = @Translation("Wiki Search Block"),
 *   category = @Translation("Search"),
 * )
 */
class WikiSearchBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build[] = [
      '#theme' => 'search_block',
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
