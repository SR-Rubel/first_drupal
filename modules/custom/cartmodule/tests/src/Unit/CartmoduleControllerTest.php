<?php

namespace Drupal\Tests\cartmoule\Unit;

use Drupal\cartmodule\CartService;
use Drupal\cartmodule\CheckoutService;
use Drupal\cartmodule\Controller\CartmoduleController;
use Drupal\cartmodule\OrderService;
use Drupal\Tests\UnitTestCase;
use Hoa\Iterator\Mock;
use Laminas\Diactoros\Response\RedirectResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;


class CartmoduleControllerTest extends UnitTestCase {

  protected OrderService|MockObject $orderService;

  protected CartService|MockObject $cartService;

  protected CheckoutService|MockObject $checkoutService;

  protected CartmoduleController $cartmoduleController;

  protected CartmoduleController $controller;

  protected Request|MockObject $request;

  protected ContainerInterface|MockObject $container;

  protected array $data;

  protected function setUp(): void {
    $this->orderService = $this->createMock(OrderService::class);
    $this->cartService = $this->createMock(CartService::class);
    $this->checkoutService = $this->createMock(CheckoutService::class);
    $this->request = $this->createMock(Request::class);

    $this->container = $this->createMock(ContainerInterface::class);


    $this->container->expects($this->any())
      ->method('get')
      ->withConsecutive(
        ['cartmodule.OrderService'],
        ['cartmodule.CartService'],
        ['cartmodule.CheckoutService']
      )
      ->willReturn($this->orderService, $this->cartService, $this->checkoutService);

    $this->controller =  $this->createMock(CartmoduleController::class);

    //    $this->request->request = $this->createMock(ParameterBag::class);
    $this->cartmoduleController = CartmoduleController::create($this->container);
    $this->data = [
      'book_id' => 1,
      'quantity' => 2,
//      'created' => time(),
    ];
    parent::setUp();
  }
  public function testAddToCart()
  {
    $this->request->method('get')->withConsecutive(['book_id'],['quantity'])
      ->willReturn($this->data['book_id'],$this->data['quantity']);
    $this->cartService->expects($this->once())
      ->method('addToCart')
      ->with($this->data);

    $this->controller->expects($this->once())
      ->method('redirect')
      ->with(
        $this->equalTo('entity.node.canonical'),
        $this->equalTo(['node' => $this->data['book_id']])
      )
      ->willReturn(new RedirectResponse('/node/' .  $this->data['book_id']));

    $this->cartmoduleController->addToCart($this->request);
  }
}
