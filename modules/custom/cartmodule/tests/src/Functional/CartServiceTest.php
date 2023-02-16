<?php
//
//namespace Drupal\Tests\cartmodule\Functional;
//
//use Drupal\Tests\BrowserTestBase;
//
///**
// * Test the module settings page
// *
// * @group my_module
// */
//class CartTest extends BrowserTestBase {
//
//  /**
//   * The modules to load to run the test.
//   *
//   * @var array
//   */
//  public static $modules = [
//    'user',
//    'cartmodule',
//  ];
//
//  /**
//   * {@inheritdoc}
//   */
//  protected function setUp() {
//    parent::setUp();
//  }
//}


namespace Drupal\Tests\cartmodule\Functional;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\cartmodule\CartService;

/**
 * Test case for the CartService.
 *
 * @group cartmodule
 */
class CartServiceTest extends BrowserTestBase {

  protected $defaultTheme = 'bookshop';
  protected $profile = 'standard';



  protected static $modules  = [
    'node',
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
  }
}

