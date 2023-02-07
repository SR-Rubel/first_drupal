<?php

namespace Drupal\cartmodule\Plugin\Block;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class CartBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  /**
   * @var Connection $db
   */
  protected Connection $db;
  /**
   * @var RendererInterface $render_service
   */
  protected RendererInterface $render_service;
  protected EntityTypeManagerInterface $entityManager;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param AccountInterface $account
   * @param RendererInterface $render_service
   * @param EntityTypeManagerInterface $entityManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $conn, RendererInterface $render_service, EntityTypeManagerInterface $entityManager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->db = $conn;
    $this->render_service = $render_service;
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }

  public function build()
  {
    // renderer service which is need to print array of render inside another render array
    $content = [];

    // counting how many product added to cart
    $query = $this->db->select('cartmodule', 't');
    $query->addExpression('SUM("quantity")');
    $count = $query->execute()->fetchField();

    // getting added cart item from database
    $title_list = [];
    $query = $this->db->select('cartmodule', 't');
    $result = $query->condition('t.id', 0, '<>')->fields('t', ['uid', 'quantity', 'uid', 'book_id'])->execute();

    // looping through the result for getting book related to the cart
    foreach ($result as $record) {
      $book = Node::load($record->book_id);
      $cart_view = $this->entityManager->getViewBuilder('node')->view($book, 'cart_view', 'en');
      $wrapper = [
        '#type' => 'container',
        '#attributes' => ['class' => 'my-custom-class'],
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        'child_1' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $this->render_service->renderPlain($cart_view),
        ],
        'child_2' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => $record->quantity,
        ]
      ];
      array_push($title_list, $wrapper);
    }


    $items = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#theme' => 'item-list',
      '#value' => $this->render_service->renderPlain($title_list),
      // '#attributes' => ['class' => 'dropdown-menu', "aria-labelledby" => "dropdownMenuButton1"],
      '#cache' => [
        'max-age' => 0,
      ]
    ];

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
