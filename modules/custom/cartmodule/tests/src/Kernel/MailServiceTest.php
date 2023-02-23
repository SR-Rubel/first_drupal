<?php

namespace Drupal\Tests\Kernel;

use Drupal;
use Drupal\cartmodule\Mail;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;
Use \Drupal\Core\Config\ConfigFactoryInterface;


class MailServiceTest extends KernelTestBase {

  use UserCreationTrait;

  protected static $modules = [
    'cartmodule',
    'user',
  ];

  protected MailManagerInterface $mailManager;
  protected AccountProxyInterface $user;
  protected MessengerInterface $messenger;
  protected Mail $mail;
  protected ConfigFactoryInterface $configFactory;
  protected User $authUser;
  protected int $uid;
  protected array $result;
  protected int $orderNumber;
  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->mailManager = Drupal::service('plugin.manager.mail');
    $this->user = Drupal::currentUser();
    $this->messenger = Drupal::messenger();
    $this->mail =  Drupal::service('cartmodule.mail');
    $this->installEntitySchema('user');
    // setting data
    $authUser = User::create([
      'name' => 'testuser',
      'mail' => 'test@example.com',
      'status' => 1,
    ]);
    $authUser->save();
    $this->authUser = $authUser;
    $this->uid = $authUser->id();
    $this->orderNumber = 2;

    //login the user
    $this->setCurrentUser($authUser);
    $config = \Drupal::configFactory()->getEditable('system.site');
    $config->set('mail', 'bookshop@mail.com');
    $config->save();
    $this->result = [
        "id" => "cartmodule_order",
        "module" => "cartmodule",
        "key" => "order",
        "to" => "test@example.com",
        "from" => "bookshop@mail.com",
        "reply-to" => null,
        "langcode" => "en",
        "params" => [
          "message" => "hello {$authUser->name->value} !. Thanks for placing order. Your order tacking number is $this->orderNumber",
          "title" => "Your order has been placed",
        ],
        "send" => true,
        "subject" => "Your order has been placed",
        "body" => "hello {$authUser->name->value} !. Thanks for placing order. Your order tacking number is $this->orderNumber\n",
//        "headers" => [
//          "MIME-Version" => "1.0",
//          "Content-Type" => "text/plain; charset=UTF-8; format=flowed; delsp=yes",
//          "Content-Transfer-Encoding" => "8Bit",
//          "X-Mailer" => "Drupal",
//          "Return-Path" => "bookshop@mail.com",
//          "Sender" => "bookshop@mail.com",
//          "From" => "bookshop@mail.com",
//        ],
        "result" => true,
    ];
  }

  /**
   * Test the sendMailToCurrentUser method.
   */
  public function testSendMailToCurrentUser() {
    $user = Drupal::currentUser();
    $this->assertEquals('bookshop@mail.com',\Drupal::config('system.site')->get('mail'));

    // creating data for sending email
    $key = 'order'; // Replace with Your key
    $params['message'] = "Thanks for placing order. Your order tacking number is $this->orderNumber";
    $params['title'] = "Your order has been placed";
    $res = $this->mail->sendMailToCurrentUser($params, $key);
    $res = array_diff_key($res,array_flip(['headers','to']));
    // making sure is the return data is equal to the
    $this->assertEquals($res,$this->result);
  }

}
