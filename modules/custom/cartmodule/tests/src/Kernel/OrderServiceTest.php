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
    $this->user = Drupal::currentUser();
    $this->messenger = Drupal::messenger();
    $this->db = Drupal::database();
    $this->orderService = Drupal::service('cartmodule.OrderService');


//    $user = User::create([
//      'name' => 'testuser',
//      'mail' => 'testuser@example.com',
//      'status' => 1,
//    ]);
//    $user->save();
//
//    // Log in as the created user.
//    $this->container->get('session')->set('uid', $user->id());
//    $this->container->get('session')->set('testsession', TRUE);

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
    $order =233;
    $order_res = $order->save();
    $this->assertEquals(TRUE,(bool)$order_res);
  }
}
