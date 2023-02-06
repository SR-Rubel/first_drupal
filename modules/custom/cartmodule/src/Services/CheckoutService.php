<?php

namespace Drupal\cartmodule\Services;
use Drupal\node\Entity\Node;

/**
 * @file
 * this class is responsible for doing order task
 */
class  CheckoutService extends BaseService
{
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
}
