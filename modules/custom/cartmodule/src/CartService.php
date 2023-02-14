<?php

namespace Drupal\cartmodule;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @file
 * this class is responsible for doing order task
 */
class  CartService
{
  protected AccountInterface $user;
  protected Connection $db;
  protected MessengerInterface $messenger;

  public function __construct(AccountInterface $user, Connection $db, MessengerInterface $messenger){
    $this->user = $user;
    $this->db = $db;
    $this->messenger = $messenger;
  }
  public function addToCart($data) : bool
  {
    $data['uid'] = $this->user->id();
//    // check weather data already exits in database
//    $query = $this->db->select('cartmodule', 'c');
//    $query->fields('c', ['quantity']);
//    $query->condition('book_id', $data['book_id']);
//    $query->condition('uid', $data['uid']);
//    $result = $query->execute()->fetchField();
//
//    if ($result) {
//      $this->db->update('cartmodule')->fields(['quantity' => $result + $data['quantity']])
//        ->condition('book_id', $data['book_id'])
//        ->condition('uid', $data['uid'])
//        ->execute();
//      $this->messenger->addMessage('Added to the previous item');
//    } else {
      $this->db->insert('cartmodule')->fields($data)->execute();
      $this->messenger->addMessage('book added to the cart');
//    }
    return true;
  }

  public function deleteFromCart($request)
  {
    $res = $this->db->delete('cartmodule')
      ->condition('book_id', $request->request->get('book_id'))
      ->condition('uid', $this->user->id())
      ->execute();
    $this->messenger->addWarning('Book removed from cart');
    return $res;
  }

}
