<?php
/**
 * @file
 * Generates markup to be displayed . Functionlity in this Controller is
 * wired to Drupal in mymodule.routing.yml.
 */

 namespace Drupal\mymodule\Controller;

 use Drupal\Core\Controller\ControllerBase;

 class FirstController extends ControllerBase {
  public function simpleContent() {
    return [
      '#type' => 'markup',
      '#markup' => 'hello world! this my first drupal controller',
    ];
  }
 }