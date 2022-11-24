<?php
/**
 * @file
 * A form to collect an email address for RSVP details
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

 class RSVPForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'rsvplist_email_form';
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    if(!is_null($node)) {
      $nid = $node->id();
    }
    else{
      $nid = 0;
    }

    $form['email'] = [
      '#type' => 'textfield',
      "#title" => "email address",
      "#size" => 25,
      "#description" => "We will send updates to the email address you",
      "#required" => true,
    ];
    $form['password'] = [
      '#type' => 'password',
      "#title" => "Password",
      "#size" => 25,
      "#description" => "Give your password here",
      "#required" => true,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'RSVP',
    ];
    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state){
    $value = $form_state->getValue('email');
    if(!(\Drupal::service('email.validator')->isValid($value))) {
      $form_state->setErrorByName('email',$this->t('Your email %mail is not valid',['%mail'=>$value]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $submitted_email = $form_state->getValue('email');
    $this->messenger()->addMessage("Hey this form is working! your just entered ".$submitted_email);
  }
  
 }