<?php

namespace Drupal\cartmodule;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;

class Mail
{
  /**
   * @var MailManagerInterface $mailManager
   */
  private MailManagerInterface $mailManager;
  /**
   * @var AccountInterface $user
   */
  private AccountProxyInterface $user;
  private MessengerInterface $messenger;

  /**
   * @param MailManagerInterface $mailManager
   * @param AccountProxyInterface $currentUser
   */
  public function __construct(MailManagerInterface $mailManager, AccountProxyInterface $currentUser, MessengerInterface $messenger)
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
    $name = $this->user->getAccountName();
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
