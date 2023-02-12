<?php

namespace Drupal\cartmodule\Form;

use Drupal\cartmodule\CartService;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * this class is for add book into cart
 */

/**
 * Class AddCartForm.
 *
 * @package Drupal\cartmodule\Form
 */
class  AddCartForm extends FormBase implements ContainerInjectionInterface {
  protected CartService $cartService;
  protected CurrentPathStack $currentPathStack;
  protected AccountInterface $user;
  protected BlockManagerInterface $blockManager;
  public function  __construct(CartService $cartService, CurrentPathStack $currentPathStack, AccountInterface $user,BlockManagerInterface $blockManager){
    $this->cartService =  $cartService;
    $this->currentPathStack = $currentPathStack;
    $this->user = $user;
    $this->blockManager = $blockManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cartmodule.CartService'),
      $container->get('path.current'),
      $container->get('current_user'),
      $container->get('plugin.manager.block')
    );
  }

  public function getFormId(): string {
    return 'add_to_cart_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['quantity'] = ['#type' => 'number', '#title' => 'quantity'];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to cart'),
      '#attributes' => ['class' => ['btn btn-dark']],
      '#ajax' => [
        'callback' => '::submitAjaxFrom',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Submitting...'),
        ],
      ],
    ];
    return $form;
  }
  public function submitAjaxFrom(array &$form, FormStateInterface $form_state) {
    $current_path =  $this->currentPathStack->getPath();
    $arguments = explode('/', $current_path);
    $node_id = end($arguments);
    $data = [
      'book_id' => $node_id,
      'quantity' => $form_state->getValue('quantity'),
      'uid' => $this->user->id(),
      'created' => time()
    ];
    $this->cartService->addToCart($data);

    $plugin_block = $this->blockManager->createInstance('cart_block');
    $render = $plugin_block->build();

    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.cart',$render));
    return $response;
  }
  public  function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

}
