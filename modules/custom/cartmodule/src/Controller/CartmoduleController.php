<?php

/**
 * @file
 * this file is created for cart module controller stuff
 */

namespace Drupal\cartmodule\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

class CartmoduleController extends ControllerBase
{
  public function addToCart(Request $request)
  {
  }

  public function deleteFromCart(Request $request)
  {

  }

  public function checkout()
  {

  }

  public function confirmed(Request $request)
  {

  }

  public function test()
  {
    dd($this->currentUser());
  }
}
