<?php
namespace Drupal\mymodule\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the Pen entity.
 *
 * @ingroup pen
 *
 * @ContentEntityType(
 *   id = "pen",
 *   label = @Translation("Pen"),
 *   base_table = "pen",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */

 class Pen extends ContentEntityBase implements ContentEntityInterface{

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel('ID')
      ->setDescription('The ID of the Advertiser entity.')
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel('UUID')
      ->setDescription('The UUID of the Advertiser entity.')
      ->setReadOnly(TRUE);

    return $fields;
  }
 }