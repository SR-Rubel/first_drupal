<?php

/**
 * @file
 * A form to collect an email address for RSVP details
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Exception;

class RSVPForm extends FormBase
{
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
    if (!is_null($node)) {
      $nid = $node->id();
    } else {
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
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $value = $form_state->getValue('email');
    if (!(\Drupal::service('email.validator')->isValid($value))) {
      $form_state->setErrorByName('email', $this->t('Your email %mail is not valid', ['%mail' => $value]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // $submitted_email = $form_state->getValue('email');
    // $this->messenger()->addMessage("Hey this form is working! your just entered " . $submitted_email);
    try{
      //initiate variable to save
      $uid = \Drupal::currentUser()->id();

      //getting full object from user id
      $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      //get entered values from the form
      $nid = $form_state->getValue('nid');
      $email = $form_state->getValue('email');
      $current_time = \Drupal::time()->getRequestTime();

      //start to build a query builder object $query
      $query = \Drupal::database()->insert('rsvplist');
      
      // Specify the fields that query will insert into
      $query->fields([
        'uid',
        'nid',
        'mail',
        'created',
      ]);

      // set values in the field
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);
      // executing the query we have build
      $query->execute();
      // showing success message
      $this->messenger()->addMessage("Form submitted successfully");
    }
    catch(Exception $e){
      dd($e->getMessage());
    }
  }
}
