<?php

/**
 * @file
 * this file contain a custom block logic
 */

namespace Drupal\mymodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * provides a block with simple text
 * @Block(
 *  id = "mymodule_block",
 *  admin_label = @Translation("test block")
 * )
 */
class MymoduleBlock extends BlockBase implements TrustedCallbackInterface {
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
    // $output = '<h1>Some random string: '.rand(1,10000).'</h1>';
    $build['normal'] = [
      '#markup' => '<p>this is simple text</p>',
      // '#cache' => [
      //   'tags' => [
      //     'node:16',
      //   ],
      //   'max-age' => 0,
      // ]
    ];
    $build['complex'] =[
      '#lazy_builder' => [static::class.'::lazyBuilderComplexData',[]],
      '#create_placeholder' => TRUE,
    ];
    // $build['complex'] = self::lazyBuilderComplexData();
    return $build;
  }
  public static function lazyBuilderComplexData(){
    sleep(5);
    return [
      '#markup' => 'this text takes time to load',
    ];
  }
  public static function trustedCallbacks()
  {
    return ['lazyBuilderComplexData'];
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