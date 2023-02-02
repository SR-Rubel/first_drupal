<?php

/**
 * @file
 * this file is created for cart module controller stuff
 */

namespace Drupal\cartmodule\Controller;

use Drupal\Core\Database\Database;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

class CartmoduleController extends ControllerBase
{
  private $user;
  private $conn;
  private $msg;
  private $db;

  public function __construct()
  {
    $this->user = \Drupal::currentUser()->id();
    $this->conn = Database::getConnection();
    $this->msg = \Drupal::messenger();
    $this->db = \Drupal::database();
  }

  public function addToCart(Request $request)
  {
    // dd($request);
    $data = array(
      'book_id' => $request->request->get('book_id'),
      'quantity' => $request->request->get('quantity'),
      'uid' => $this->user,
      'created' => time()
    );

    // check weather data already exits in database
    $query = \Drupal::database()->select('cartmodule', 'c');
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
    ->condition('uid', $this->user)
    ->execute();
    $this->msg->addWarning('Book removed from cart');
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }
  public function checkout()
  {
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id','quantity']);
    $query->condition('uid', $this->user);
    $result = $query->execute();
    $total_cost = 0;
    foreach($result as $record)
    {
      $node = Node::load($record->book_id);
      $book_price = $node->field_price->value;
      $total_cost += $book_price*$record->quantity;
    }
    // dd($total_cost);
    $block_manager = \Drupal::service('plugin.manager.block');
    $plugin_block = $block_manager->createInstance('cart_block');
    $render = $plugin_block->build();
    // dd($render);
    return [
      '#theme' => 'checkout',
      '#content' => ['books' => $render['#content']['items'],'total'=>$total_cost]
    ];
    return $render['#content']['items'];
  }
  public function confirmed(Request $request)
  {
    $order = Node::create(['type' => 'order']);
    
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id', 'quantity']);
    $query->condition('uid', $this->user);
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
    $order->uid = $this->user;
    $order_res = $order->save();

    // make empty the user cart
    if($order_res){
    $res = $this->conn->delete('cartmodule')
    ->condition('uid', $this->user)
    ->execute();
    }
    // sending mail to user after order
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'cartmodule';
    $key = 'order'; // Replace with Your key
    $to = \Drupal::currentUser()->getEmail();
    $name = \Drupal::currentUser()->getAccount()->name;
    $params['message'] = "hello $name ! Thanks for placing order. Your order tacking number is ".$order->id();
    $params['title'] = "Your order has been placed";
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    $this->msg->addMessage('mail sent');
    return [
    '#theme' => 'thanks',
    ];
  }
}