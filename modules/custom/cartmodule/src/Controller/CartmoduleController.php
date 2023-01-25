<?php

/**
 * @file
 * this file is created for cart module controller stuff
 */

namespace Drupal\cartmodule\Controller;

use Drupal\Core\Database\Database;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class CartmoduleController extends ControllerBase
{
  private $user;
  private $conn;
  private $msg;

  public function __construct()
  {
    $this->user = \Drupal::currentUser()->id();
    $this->conn = Database::getConnection();
    $this->msg = \Drupal::messenger();
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
}
