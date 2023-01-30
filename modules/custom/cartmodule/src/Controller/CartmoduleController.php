<?php

/**
 * @file
 * this file is created for cart module controller stuff
 */

namespace Drupal\cartmodule\Controller;

use Drupal\Core\Database\Database;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class CartmoduleController extends ControllerBase
{
  private $user;
  private $conn;
  private $msg;
  private $db;

  private function calculateTotal($save_details = false, $order_id = 0)
  {
    $query = $this->db->select('cartmodule', 'c');
    $query->fields('c', ['book_id', 'quantity']);
    $query->condition('uid', $this->user);
    $result = $query->execute();
    $total_cost = 0;
    foreach ($result as $record) {
      $node = Node::load($record->book_id);
      $book_price = $node->field_price->value;
      $total_cost += $book_price * $record->quantity;
      if ($save_details) {
        $data['book_id'] =  $record->book_id;
        $data['order_id'] =  $order_id;
        $data['unit_price'] =  $book_price;
        $data['quantity'] =  $record->quantity;
        $this->conn->insert('order_details')->fields($data)->execute();
      }
    }
    return $total_cost;
  }

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

    if ($result) {
      $this->conn->update('cartmodule')->fields(['quantity' => $result + $data['quantity']])
        ->condition('book_id', $data['book_id'])
        ->condition('uid', $data['uid'])
        ->execute();
      $this->msg->addMessage('Added to the previous item');
    } else {
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
    $total_cost = $this->calculateTotal();

    $block_manager = \Drupal::service('plugin.manager.block');
    $plugin_block = $block_manager->createInstance('cart_block');
    $render = $plugin_block->build();

    return [
      '#theme' => 'checkout',
      '#content' => ['books' => $render['#content']['items'], 'total' => $total_cost]
    ];
    return $render['#content']['items'];
  }
  public function confirmed()
  {
    // order entry create in order table
    $transaction = $this->conn->startTransaction();
    try {
      $data['uid'] =  $this->user;
      $data['address'] = 'demo address will be set later';
      $order_id = $this->conn->insert('orders')->fields($data)->execute();
      $total = $this->calculateTotal(true, $order_id);

      // updating the order total
      $this->conn->update('orders')->fields(['total' => $total])
        ->condition('id', $order_id)->execute();

      // make empty the user cart
      $res = $this->conn->delete('cartmodule')
        ->condition('uid', $this->user)
        ->execute();
    } catch (Exception $e) {
      $transaction->rollBack();
    }
    unset($transaction);

    // sending mail to user for about order confirmation
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'cartmodule';
    $key = 'order'; // Replace with Your key
    $to = \Drupal::currentUser()->getEmail();
    $params['message'] = "you have ordered books form our bookshop site. Your total amount of cost is: {$total}. Please pay on delivery";
    $params['title'] = "Your order has been placed";
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    $this->msg->addMessage(' mail sent');

    // returning to the thank you page
    return [
      '#theme' => 'thanks',
    ];
  }
}
