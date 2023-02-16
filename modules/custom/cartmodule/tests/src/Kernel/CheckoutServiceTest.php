<?php
namespace Drupal\tests\Kernel;
use Drupal\cartmodule\CartService;
use Drupal\cartmodule\CheckoutService;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;

class CheckoutServiceTest extends KernelTestBase{
  protected Connection $db;
  protected AccountInterface $user;
  protected BlockManagerInterface $blockManager;
  protected CartService $cartService;
  protected CheckoutService $checkoutService;


  protected int $book_id;

  protected int $quantity;

  protected array $data;

  protected function setUp() {
    parent::setUp();

    // initialize services
    $this->db = \Drupal::database();
    $this->user = \Drupal::currentUser();
    $this->blockManager = \Drupal::service('plugin.manager.block');
    $this->cartService = \Drupal::service('cartmodule.CartService');
    // creating custom service object
    $this->checkoutService = new CheckoutService($this->db,$this->user,$this->blockManager);

    $this->book_id = 1;
    $this->quantity = 2;
    $this->data = [
      'book_id' => $this->book_id,
      'quantity' => $this->quantity,
    ];
  }

  public function testCheckout(){

  }

  private function getCartResult()
  {
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id','quantity']);
    $query->condition('uid', $this->user->id());
    $result = $query->execute();
    return $result;
  }
}
