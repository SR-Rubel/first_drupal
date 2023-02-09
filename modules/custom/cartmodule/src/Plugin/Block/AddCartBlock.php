<?php

namespace Drupal\cartmodule\Plugin\Block;

use Drupal\cartmodule\CartService;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * add to cart block will add book into cart
 */

/**
 * provides a block with form for add to cart
 * @Block(
 *  id = "add_cart_block",
 *  admin_label = @Translation("Add to cart block")
 * )
 */
class AddCartBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface $formBuilder
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * @var \Drupal\cartmodule\CartService $cartService
   */
  protected CartService $cartService;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   * @param \Drupal\cartmodule\CartService $cartService
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $formBuilder, CartService $cartService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
    $this->cartService = $cartService;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   *
   * @return \Drupal\cartmodule\Plugin\Block\AddCartBlock|static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('cartmodule.CartService'),
    );
  }

  /**
   * @return array
   */
  public function build(): array {
    return $this->formBuilder->getForm('\Drupal\cartmodule\Form\AddCartForm');
  }

}
