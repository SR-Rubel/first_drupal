<?php

/**
 * @file
 * this file is created for cart module controller stuff
 */

namespace Drupal\cartmodule\Controller;

use Drupal\cartmodule\CartService;
use Drupal\cartmodule\CheckoutService;
use Drupal\cartmodule\OrderService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class CartmoduleController extends ControllerBase implements ContainerInjectionInterface
{
  protected  OrderService $orderService;
  protected  CartService $cartService;
  protected  CheckoutService $checkoutService;

  /**
   * @param OrderService $orderService
   * @param CartService $cartService
   */
  public function __construct(OrderService $orderService, CartService $cartService, CheckoutService $checkoutService)
  {
    $this->orderService = $orderService;
    $this->cartService = $cartService;
    $this->checkoutService = $checkoutService;
  }

  /**
   * @param ContainerInterface $container
   * @return CartmoduleController|static
   */
  public static function  create(ContainerInterface $container)
  {
    return new static(
      $container->get('cartmodule.OrderService'),
      $container->get('cartmodule.CartService'),
      $container->get('cartmodule.CheckoutService')
    );
  }

  public function addToCart(Request $request)
  {
    $data =  [
      'book_id' => $request->request->get('book_id'),
      'quantity' => $request->request->get('quantity'),
      'created' => time()
    ];
    $this->cartService->addToCart($data);
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }

  public function deleteFromCart(Request $request)
  {
    $this->cartService->deleteFromCart($request);
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }

  public function checkout()
  {
    return $this->checkoutService->checkout();
  }

  public function confirmed(Request $request)
  {
    return $this->orderService->placeOrder($request);
  }
  public function test(Request $request)
  {

  }
}
