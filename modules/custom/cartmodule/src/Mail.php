<?php

namespace Drupal\cartmodule;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;

class Mail
{
  /**
   * @var MailManagerInterface $mailManager
   */
  private MailManagerInterface $mailManager;
  /**
   * @var AccountInterface $user
   */
  private AccountInterface $user;
  private MessengerInterface $messenger;

  /**
   * @param MailManagerInterface $mailManager
   * @param AccountInterface $currentUser
   */
  public function __construct(MailManagerInterface $mailManager, AccountInterface $currentUser, MessengerInterface $messenger)
  {
    $this->mailManager = $mailManager;
    $this->user = $currentUser;
    $this->messenger = $messenger;
  }

  /**
   * @param $params
   * @param $key
   * @return array
   */
  public function sendMailToCurrentUser(array $params,string $key) : array
  {
    $module = 'cartmodule';
    $to = $this->user->getEmail();
    $name = $this->user->getAccount()->name;
    $params['message'] = "hello $name !. ".$params['message'];
    $langcode = $this->user->getPreferredLangcode();
    $send = true;
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    $this->messenger->addMessage('mail sent');
    return $result;
  }
//  public function sendMailToBookAuthor(array $params, Integer $author_id): bool
//  {
//    // sending mail to user after order
//    $module = 'cartmodule';
//    $key = 'order'; // Replace with Your key
//    $to = \Drupal::config('system.site')->get('mail');
//    $name = $this->user->getAccount()->name;
//    $params['message'] = "hello $name !".$params['message'];
//    $langcode = $this->user->getPreferredLangcode();
//    $send = true;
//    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
//    $this->messenger->addMessage('mail sent');
//    return (bool)$result;
//  }
}
