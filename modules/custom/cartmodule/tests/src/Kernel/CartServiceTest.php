<?php

namespace Drupal\Tests\Kernel;

use Drupal\KernelTests\KernelTestBase;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cartmodule\CartService;

class CartServiceTest extends KernelTestBase {

  protected static $modules  = [
    'cartmodule',
  ];
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * A user object.
   *
   * @var \Drupal\user\Entity\User
   */
  protected AccountInterface $user;

  /**
   * The messenger object.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The CartService object.
   *
   * @var \Drupal\cartmodule\CartService
   */
  protected CartService $cartService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void
  {
    parent::setUp();
//    $this->installConfig(['mymodule']);
    $this->installSchema('cartmodule', ['cartmodule','cartmodule_enabled']);
    $this->database = \Drupal::database();
    $this->user = \Drupal::currentUser();
    $this->messenger = \Drupal::messenger();
    $this->cartService = new CartService($this->user, $this->database, $this->messenger);
  }

  /**
   * Test the addToCart method.
   */
  public function testAddToCart() {
    $book_id = 1;
    $quantity = 2;
    $data = [
      'book_id' => $book_id,
      'quantity' => $quantity,
    ];

    // Test adding a new book to the cart.
    $this->assertTrue($this->cartService->addToCart($data));
    //    $result = $this->database->select('cartmodule', 'c')
    //      ->fields('c', ['quantity'])
    //      ->condition('book_id', $book_id)
    //      ->condition('uid', $this->user->id())
    //      ->execute()
    //      ->fetchField();
    //    $this->assertEquals($result, $quantity);
    //
    //    // Test adding the same book to the cart again.
    //    $quantity = 3;
    //    $data = [
    //      'book_id' => $book_id,
    //      'quantity' => $quantity,
    //    ];
    //    $this->assertTrue($this->cartService->addToCart($data));
    //    $result = $this->database->select('cartmodule', 'c')
    //      ->fields('c', ['quantity'])
    //      ->condition('book_id', $book_id)
    //      ->condition('uid', $this->user->id())
    //      ->execute()
    //      ->fetchField();
  }
}
