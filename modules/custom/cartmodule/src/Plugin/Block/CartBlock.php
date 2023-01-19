<?php

namespace Drupal\cartmodule\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * @file
 * this is cart block for showing count in block
 */


 /**
 * provides a block with count of cart product
 * @Block(
 *  id = "cart_block",
 *  admin_label = @Translation("Cart Block")
 * )
 */
 class CartBlock extends BlockBase{
    /**
   * {@inherit}
   */
  public function build()
  {
    $content = [];

    $query = \Drupal::database()->select('cartmodule', 't');
    $query->addExpression('SUM("quantity")');
    $count = $query->execute()->fetchField();

    $title_list = [];
    $query = \Drupal::database()->select('cartmodule', 't');
    $result = $query->condition('t.id', 0, '<>')->fields('t', ['uid', 'quantity', 'uid', 'book_id'])->execute();

    foreach ($result as $record) {
      $book = Node::load($record->book_id);
      // array_push($title_list, $book->field_name->value);
      array_push($title_list,[
        '#type' => 'html_tag',
        '#tag' => 'li',
        '#attributes' => ['class' => 'dropdown-item'],
        '#value' => $book->field_name->value
      ]);
    }

    $render_service = \Drupal::service('renderer');

    $items = [
      '#type' => 'html_tag',
      '#tag' => 'ul',
      '#value' => $render_service->renderPlain($title_list),
      '#attributes' => ['class' => 'dropdown-menu', "aria-labelledby" => "dropdownMenuButton1"],
      '#cache' => [
        'max-age' => 0,
      ]
    ];
    // $items = [
    //   '#theme' => 'item_list',
    //   '#list_type' => 'ul',
    //   '#items' => $title_list,
    //   '#attributes' => ['class' => 'dropdown-menu', "aria-labelledby" => "dropdownMenuButton1"],
    //   '#cache' => [
    //     'max-age' => 0,
    //   ]
    // ];

    // making product list
    $content['count'] = $count;
    $content['items'] = $items;
    return [
      '#theme' => 'cartblock',
      '#content' => $content,
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }
 }