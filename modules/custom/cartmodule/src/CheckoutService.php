<?php

namespace Drupal\cartmodule;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * @file
 * this class is responsible for doing order task
 */
class  CheckoutService
{
  protected Connection $db;
  protected AccountInterface $user;
  protected BlockManagerInterface $blockManager;
  public function __construct(Connection $db,AccountInterface $user,BlockManagerInterface $blockManager)
  {
    $this->db = $db;
    $this->user = $user;
    $this->blockManager = $blockManager;
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

    $plugin_block = $this->blockManager->createInstance('cart_block');
    $render = $plugin_block->build();
    return [
      '#theme' => 'checkout',
      '#content' => ['books' => $render['#content']['items'],'total'=>$total_cost]
    ];
  }
}
