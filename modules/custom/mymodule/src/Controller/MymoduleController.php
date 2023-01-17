<?php

/**
 * @file
 * Generate a node
 */

namespace Drupal\mymodule\Controller;

use Drupal;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;

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

  // ====practise section =======
  public function createEntityPractice(){
    // creating a configuration entity
    $type = \Drupal::service('plugin.manager.block');
    dd($type);
    $details = array('id'=>'progblock','title'=> 'programmed block','plugin'=>$type,'label'=>'progblock');
    $configEntity = Drupal\block\Entity\Block::create($details);
//    $configEntity = Drupal::entityTypeManager()->getStorage('block')->load('progblock');

    $configEntity->save();
//    dd($configEntity);
    dd($configEntity);
    // creating a content entity
  }
  public function test(){

    // $entity = \Drupal::entityTypeManager()->getStorage('node')->load(15);
    // dd(\Drupal::entityTypeManager()->getAccessControlHandler('node'));
    // dd($entity->access('book'));


    // $nodeStorage = \Drupal::entityTypeManager()->getStorage('node
    // $ids = $nodeStorage->getQuery()
    // ->condition('status', 1)
    // ->condition('type', 'book') // type = bundle id (machine name)
    // ->condition('nid','<',5)
    // //->sort('created', 'ASC') // sorted by time of creation
    // ->pager(15) // limit 15 items
    // ->execute();
    // dd($ids);

//    $entity = \Drupal::entityTypeManager()->getStorage('node')->load(17);
//    $langcode = Drupal::languageManager()->getLanguage(Language::TYPE_CONTENT);
//    $context = array('operation' => 'node_tokens');
//    $translation = \Drupal::service($entity,'en',$context);
//    dd($translation);


//    dd(Drupal::entityTypeManager()->getStorage('node')->load(15)->toUrl()->toString());
    $this->createEntityPractice();
  }
  public function createNodeType(string $type){
    // getting all entity of distinct type (author)
    $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'author']);
    shuffle($authors);
    dd($authors[0]);

    // getting all book entity of distinct type
    $authors = Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type'=>'book']);
  }
  public function view(){
    $content = [];
    $content['name'] = 'My name is Mr.x';
    return [
      '#theme' => 'landing',
      '#content' => $content,
    ];
  }
}
