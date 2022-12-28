<?php

/**
 * @file
 * Generate a node 
 */

namespace Drupal\mymodule\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;

class MymoduleController extends ControllerBase
{
  public function generateBooks()
  {
    // // getting all entity of distinct type
    // $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'author']);
    // dd($authors[0]);
    for ($i = 1; $i < 100; $i++) {
      $node = Node::create(['type' => 'book']);
      $node->set('field_name', "test node $i");
      $node->set('title', "test node $i");
      $node->set('nid', $i + 100);
      $node->status = 1;
      $node->save();
    }
  }
  public function deleteBooks()
  {
    //delete node programmatically
    for ($i = 1; $i < 100; $i++) {
      $node = Node::load($i + 100);
      $node->delete();
    }
  }
  public function getAllEntity(string $type){
    // getting all entity of distinct type (author)
    $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'author']);
    shuffle($authors);
    dd($authors[0]);

    // getting all book entity of distinct type
    $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'book']);
  }
  public function test(){

    $entity = \Drupal::entityTypeManager()->getStorage('node')->load(15);
    dd(\Drupal::entityTypeManager()->getAccessControlHandler('node'));
    dd($entity->access('book'));


    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $nodeStorage->getQuery()
    ->condition('status', 1)
    ->condition('type', 'book') // type = bundle id (machine name)
    ->condition('nid','<',5)
    //->sort('created', 'ASC') // sorted by time of creation
    ->pager(15) // limit 15 items
    ->execute();
    dd($ids);
  }
  public function createNodeType(string $type){
    // getting all entity of distinct type (author)
    $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'author']);
    shuffle($authors);
    dd($authors[0]);

    // getting all book entity of distinct type
    $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'book']);
  }
}
 