<?php

namespace Drupal\wikivisually_home\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Defines a wikivisually home block type.
 *
 * @Block(
 *   id = "wikivisually_home_block",
 *   admin_label = @Translation("Wikivisually Home Block"),
 *   category = @Translation("Wikivisually"),
 * )
 */
class WikivisuallyHomeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $config = $this->getConfiguration();

    $form['wikivisually'] = [
      '#type' => 'fieldset',
      '#title' => t('Youtube channel settings'),
    ];

    $form['wikivisually']['title'] = [
      '#type' => 'textfield',
      '#title' => t('header link to the home page'),
      '#size' => 40,
      '#default_value' => $config['title'],
      '#required' => TRUE,
    ];

    $form['wikivisually']['description'] = [
      '#type' => 'textfield',
      '#title' => t('description of the site'),
      '#size' => 400,
      '#default_value' => $config['description'],
      '#required' => TRUE,
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    foreach (['wikivisually'] as $fieldset) {
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
    $title = $config['title'];
    $description = $config['description'];

    $build[] = [
      '#theme' => 'wikivisually_home',
      '#title' => $title,
      '#description' => $description,
    ];

    $build['#attached']['library'][] = 'wikivisually_home/wikivisually_home';

    return $build;
  }
}
