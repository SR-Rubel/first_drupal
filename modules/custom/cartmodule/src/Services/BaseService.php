<?php

namespace Drupal\cartmodule\Services;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseService extends ControllerBase implements ContainerInjectionInterface {
  protected $user;
  protected $conn;
  protected $msg;
  protected $db;

  protected $account;
  protected $mailManager;
  protected $block_manager;

  protected $render_service;
  protected $entityManager;

  public function __construct(Connection $conn,AccountInterface $currentUser, MessengerInterface $msg,MailManagerInterface $mailManager, BlockManagerInterface $block_manager,RendererInterface $render_service,EntityTypeManagerInterface $entityManager)
  {
    $this->user = $currentUser;
    $this->conn = $conn;
    $this->msg = $msg;
    $this->db = $conn;
    $this->mailManager = $mailManager;
    $this->block_manager = $block_manager;

    $this->render_service = $render_service;
    $this->entityManager = $entityManager;
  }
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('plugin.manager.mail'),
      $container->get('plugin.manager.block'),
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }
}
