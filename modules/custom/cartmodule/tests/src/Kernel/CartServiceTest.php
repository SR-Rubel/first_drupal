<?php

namespace Drupal\Tests\Kernel;

use Drupal;
use Drupal\KernelTests\KernelTestBase;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cartmodule\CartService;

class CartServiceTest extends KernelTestBase {

  protected static $modules = [
    'cartmodule',
  ];

  protected Connection $db;

  protected AccountInterface $user;

  protected MessengerInterface $messenger;

  protected CartService $cartService;


  protected int $book_id;

  protected int $quantity;

  protected array $data;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cartmodule', ['cartmodule', 'cartmodule_enabled']);
    $this->db = Drupal::database();
    $this->user = Drupal::currentUser();
    $this->messenger = Drupal::messenger();
    $this->cartService = new CartService($this->user, $this->db, $this->messenger);

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
  public function testAddToCart() {
    // Test adding a new book to the cart.
    $this->assertTrue($this->cartService->addToCart($this->data));
    $resultQuantityPrev = $this->getQuantity($this->book_id);
    $this->assertEquals($resultQuantityPrev, $this->quantity);

    // Test adding the same book to the cart again.
    $quantity = 3;
    $this->data['quantity'] = $quantity;
    $this->assertTrue($this->cartService->addToCart($this->data));

    // Test for checking same item quantity added to previous or not
    $resultQuantity = $this->getQuantity($this->book_id);
    $this->assertEquals($quantity + $resultQuantityPrev, $resultQuantity);
  }

  public function getQuantity($book_id) {
    return $this->db->select('cartmodule', 'c')
      ->fields('c', ['quantity'])
      ->condition('book_id', $book_id)
      ->condition('uid', $this->user->id())
      ->execute()
      ->fetchField();
  }

  public function testDeleteFromCart() {
    // Test adding a new book to the cart.
    $this->assertTrue($this->cartService->addToCart($this->data));
    $result = $this->db->delete('cartmodule')
      ->condition('book_id', $this->book_id)
      ->condition('uid', $this->user->id())
      ->execute();
    $this->assertEquals(1, $result);
  }
}
