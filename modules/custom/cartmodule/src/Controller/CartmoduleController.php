<?php

/**
 * @file
 * this file is created for cart module controller stuff
 */

namespace Drupal\cartmodule\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Database\Connection;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\cartmodule\Plugin\Block\CartBlock;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class CartmoduleController extends ControllerBase implements ContainerInjectionInterface
{
  private $user;
  private $conn;
  private $msg;
  private $db;

  protected $account;
  protected $mailManager;
  protected $block_manager;

  protected $render_service;
  protected $entityManager;

  public function __construct(Connection $conn,AccountInterface $currentUser, MessengerInterface $msg,MailManagerInterface $mailManager, BlockManagerInterface $block_manager,RendererInterface $render_service,EntityTypeManagerInterface $entityManager)
  {
    $this->user = $currentUser;
    $this->conn = $conn;
    $this->msg = $msg;
    $this->db = $conn;
    $this->mailManager = $mailManager;
    $this->block_manager = $block_manager;

    $this->render_service = $render_service;
    $this->entityManager = $entityManager;
  }
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('plugin.manager.mail'),
      $container->get('plugin.manager.block'),
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }
  public function addToCart(Request $request)
  {
    // dd($request);
    $data = array(
      'book_id' => $request->request->get('book_id'),
      'quantity' => $request->request->get('quantity'),
      'uid' => $this->user->id(),
      'created' => time()
    );

    // check weather data already exits in database
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['quantity']);
    $query->condition('book_id', $data['book_id']);
    $query->condition('uid', $data['uid']);
    $result = $query->execute()->fetchField();

    if($result){
      $this->conn->update('cartmodule')->fields(['quantity'=>$result+$data['quantity']])
      ->condition('book_id', $data['book_id'])
      ->condition('uid', $data['uid'])
      ->execute();
      $this->msg->addMessage('Added to the previous item');
    }
    else{
      $this->conn->insert('cartmodule')->fields($data)->execute();
      $this->msg->addMessage('book added to the cart');
    }
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }

  public function deleteFromCart(Request $request)
  {
    $res = $this->conn->delete('cartmodule')
    ->condition('book_id', $request->request->get('book_id'))
    ->condition('uid', $this->user->id())
    ->execute();
    $this->msg->addWarning('Book removed from cart');
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }
  public function checkout()
  {
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id','quantity']);
    $query->condition('uid', $this->user->id());
    $result = $query->execute();
    $total_cost = 0;
    foreach($result as $record)
    {
      $node = Node::load($record->book_id);
      $book_price = $node->field_price->value;
      $total_cost += $book_price*$record->quantity;
    }
    // dd($total_cost);
    $plugin_block = $this->block_manager->createInstance('cart_block');
    $render = $plugin_block->build();
    // dd($render);
    return [
      '#theme' => 'checkout',
      '#content' => ['books' => $render['#content']['items'],'total'=>$total_cost]
    ];
  }
  public function confirmed(Request $request)
  {
    $order = Node::create(['type' => 'order']);

    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id', 'quantity']);
    $query->condition('uid', $this->user->id());
    $result = $query->execute();
    $total_cost = 0;

    // creating order details for every product added to the cart
    foreach ($result as $record) {
      $node = Node::load($record->book_id);
      $book_price = $node->field_price->value;
      $total_cost += $book_price * $record->quantity;

      $order_details = Node::create(['type' => 'order_details']);
      $order_details->field_books[] = $record->book_id;
      $order_details->field_integer = $record->quantity;
      $order_details->title = 'details';
      $order_details->save();
      $order->field_order_details[] = ['target_id'=>$order_details->id()];
    }
    $order->title = 'order';
    $order->field_text = $request->request->get('address');
    $order->field_price = $total_cost;
    $order->uid = $this->user->id();
    $order_res = $order->save();

    // make empty the user cart
    if($order_res){
    $res = $this->conn->delete('cartmodule')
    ->condition('uid', $this->user->id())
    ->execute();
    }
    // sending mail to user after order
    $module = 'cartmodule';
    $key = 'order'; // Replace with Your key
    $to = $this->user->getEmail();
    $name = $this->user->getAccount()->name;
    $params['message'] = "hello $name ! Thanks for placing order. Your order tacking number is ".$order->id();
    $params['title'] = "Your order has been placed";
    $langcode = $this->user->getPreferredLangcode();
    $send = true;
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    $this->msg->addMessage('mail sent');
    return [
    '#theme' => 'thanks',
    ];
  }
  public function test()
  {
    $plugin_block = new CartBlock($this->conn,$this->render_service,$this->entityManager);
    // $plugin_block = $this->block_manager->createInstance('cart_block');
    $render = $plugin_block->build();
    return $render;
  }
}
