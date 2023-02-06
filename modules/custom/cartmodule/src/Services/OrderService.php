<?php

namespace Drupal\cartmodule\Services;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * this class is responsible for doing order task
 */
class  OrderService extends BaseService{
  public $all;
  public function __construct(BaseService $baseService)
  {
    $this->all = $baseService;
  }

  public function placeOrder($request){
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
    $key = 'order'; // Replace with Your key
    $params['message'] = "Thanks for placing order. Your order tacking number is ".$order->id();
    $params['title'] = "Your order has been placed";
    $test =\Drupal::service('cartmodule.mail')->sendMailToCurrentUser($params,$key);

    return [
      '#theme' => 'thanks',
    ];
  }

  public function test()
  {
    echo 'hello';
  }
}
