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
  public function addToCart(Request $request)
  {
    // dd($request);
    $data = array(
      'book_id' => $request->request->get('book_id'),
      'quantity' => $request->request->get('quantity'),
      'uid' => \Drupal::currentUser()->id(),
      'created' => time()
    );

    $conn = Database::getConnection();
    $conn->insert('cartmodule')->fields($data)->execute();
    
    \Drupal::messenger()->addMessage('book added to the cart');
    return $this->redirect('entity.node.canonical', ['node' => $request->request->get('book_id')]);
  }
}
