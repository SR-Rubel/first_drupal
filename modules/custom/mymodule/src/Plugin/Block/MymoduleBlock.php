<?php

/**
 * @file
 * this file contain a custom block logic
 */

namespace Drupal\mymodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * provides a block with simple text
 * @Block(
 *  id = "mymodule_block",
 *  admin_label = @Translation("test block")
 * )
 */
class MymoduleBlock extends BlockBase {
  /**
   * {@inherit}
   */
  public function build()
  {
    // return [
    //   '#type' => 'view',
    //   '#name' => 'books',
    //   '#display_id' => 'all_books',
    //   '#cache' => [
    //     'tags' => [
    //       'node:16',
    //     ],
    //     'max-age' => Cache::PERMANENT,
    //   ]
    // ];
    $output = '<h1>Some random string: '.rand(1,10000).'</h1>';
    return [
      '#markup' => $output,
      '#display_id' => 'all_books',
      '#cache' => [
        'tags' => [
          'node:16',
        ],
        'max-age' => 10,
      ]
    ];
  }
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form,$form_state);
    $config = $this->getConfiguration();
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Type the message you want visitors to see'),
      '#default_value' => $config['message'] ?? '',
    ];
    $form['details'] = [
      '#type' => 'textarea',
      '#title' => $this->t('details'),
      '#description' => $this->t('Type the message you want visitors to see'),
      '#default_value' => $config['message'] ?? '',
    ];
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) : void {
    // We do this to ensure no other configuration options get lost.
    parent::blockSubmit($form, $form_state);
   
    // Here the value entered by the user is saved into the configuration.
    $this->configuration['message'] = $form_state->getValue('message');
   }
}