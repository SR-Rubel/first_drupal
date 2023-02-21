<?php

namespace Drupal\Tests\Kernel;

use Drupal;
use Drupal\cartmodule\Mail;
use Drupal\cartmodule\OrderService;
use Drupal\KernelTests\KernelTestBase;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\cartmodule\CartService;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class OrderServiceTest extends KernelTestBase {

  protected static $modules = [
    'cartmodule',
    'node',
    'user'
  ];


  protected AccountInterface $user;
  protected Mail $mail;
  protected Connection $db;
  protected OrderService $orderService;

  protected int $book_id;

  protected int $quantity;

  protected array $data;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cartmodule', ['cartmodule', 'cartmodule_enabled']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->user = Drupal::currentUser();
    $this->messenger = Drupal::messenger();
    $this->db = Drupal::database();
    $this->orderService = Drupal::service('cartmodule.OrderService');

    // setting data
    $this->book_id = 1;
    $this->quantity = 2;
    $this->data = [
      'book_id' => $this->book_id,
      'quantity' => $this->quantity,
    ];
  }

  /**
   * Test the addToCart method.
   */
  public function testPlaceOrder() {
    $order = Node::create(['type' => 'order']);
    $order->title = 'order';
    $order->field_text = 'demo address';
    $order->uid = $this->user->id();
    $order->field_price =233;
    $order_res = $order->save();
    $this->assertEquals(TRUE,(bool)$order_res);
  }
}
