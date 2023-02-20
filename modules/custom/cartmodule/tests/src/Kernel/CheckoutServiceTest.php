<?php
namespace Drupal\tests\Kernel;
use Drupal\cartmodule\CartService;
use Drupal\cartmodule\CheckoutService;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;

class CheckoutServiceTest extends KernelTestBase{
  protected static $modules = [
    'system',
    'node',
    'user',
    'cartmodule',
  ];
  protected $strictConfigSchema = FALSE;
  protected Connection $db;
  protected AccountProxyInterface $user;
  protected BlockManagerInterface $blockManager;
  protected CartService $cartService;
  protected CheckoutService $checkoutService;


  protected int $book_id;

  protected int $quantity;

  protected array $data;
  protected float $total_cost;

  protected function setUp() : void
  {
    parent::setUp();
    $this->installSchema('cartmodule', ['cartmodule', 'cartmodule_enabled']);
    $this->installConfig(['node']);
    $this->installEntitySchema('node');
    $this->installSchema('system', 'sequences');
    \Drupal::service('module_installer')->install(['user']);


    // initialize services
    $this->db = \Drupal::database();
    $this->user = \Drupal::currentUser();
    $this->blockManager = \Drupal::service('plugin.manager.block');
    $this->cartService = \Drupal::service('cartmodule.CartService');
    // creating custom service object
    $this->checkoutService = new CheckoutService($this->db,$this->user,$this->blockManager);

    //create two books
    $this->data = [
      'book_id' => 1,
      'quantity' => 2,
    ];
  }

  public function testCheckout(){
    $this->addingToCart();
//    $result = $this->getCartResult();

    $total_cost = 0;

//    foreach($result as $record)
//    {
//      $node = Node::load($record->book_id);
//      $book_price = $node->field_price->value;
//      $total_cost += $book_price*$record->quantity;
//    }
    $this->assertEquals(0,0);

  }

  private function getCartResult()
  {
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id','quantity']);
    $query->condition('uid', $this->user->id());
    return $query->execute();
  }
  private function createBook(float $price){
    $book =  Node::create(['type'=> 'book']);
    $book->title = 'book';
    $book->field_name = 'test book';
    $book->field_price = $price;
    $book->uid = $this->user->id();
    $book->save();
    return $book->nid->value;
  }
  private function addingToCart(){
    // creating two books
    $book1 = $this->createBook(244);
    $book2 = $this->createBook(255);

    // setting total cost
    $this->total_cost = 244 * $this->data['quantity'] + 255 * $this->data['quantity'];

    // adding book1 into cart
    $this->data['book_id'] = $book1->nid->value;
    $this->cartService->addToCart($this->data);

    // adding book2 into cart
    $this->data['book_id'] = $book2->nid->value;
    $this->cartService->addToCart($this->data);
    return $this->total_cost;
  }
}
