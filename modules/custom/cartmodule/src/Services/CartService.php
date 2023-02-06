<?php

namespace Drupal\cartmodule\Services;

use Drupal\Core\Controller\ControllerBase;

/**
 * @file
 * this class is responsible for doing order task
 */
class  CartService extends ControllerBase
{
  public function addToCart($request)
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

  public function deleteFromCart($request)
  {
    $res = $this->conn->delete('cartmodule')
      ->condition('book_id', $request->request->get('book_id'))
      ->condition('uid', $this->user->id())
      ->execute();
    $this->msg->addWarning('Book removed from cart');
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }

}
