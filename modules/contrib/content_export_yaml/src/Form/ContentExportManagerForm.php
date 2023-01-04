<?php

namespace Drupal\content_export_yaml\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\FileStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\Markup;
/**
 * Class ConfigImportForm.
 */
class ContentExportManagerForm extends FormBase {



//  /**
//   * {@inheritdoc}
//   */
  protected function getEditableConfigNames() {
    return [
      'content_export_yaml.manage_content_yaml',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_content_yaml_form';
  }
  public static function  ajaxBundleCallback (array &$form, FormStateInterface $form_state){
        $entity_name = $form_state->getValue('entity_name');
        $bundle_list_name = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_name);            
        $help ='<ul>';
        $help = '<li><b>Selected Entity Name : </b>'.$entity_name.'</li>' ;
        $help = $help.'</ul>';
        $form['help']['#markup'] =   $help ;
        $options = array_merge(['' => '-- None --'],[]);
        foreach(  $bundle_list_name as $key => $bundle){
          $options[$key] = $bundle['label'];
        }
        $form['bundle']['#options'] = $options;   
        return  $form;
   }
   public static function  ajaxRangeCallback (array &$form, FormStateInterface $form_state){
    $entity_name = $form_state->getValue('entity_name');
    $bundle = $form_state->getValue('bundle');
    $export = \Drupal::service('content_export_yaml.manager');
    $count = $export->getCount($entity_name,$bundle);

    $help ='<ul>';
    $help = '<li><b>Selected Entity Name : </b>'.$entity_name.'</li>' ;
    $help = $help.'<li><b>Selected Bundle Name : </b>'.$bundle.'</li>' ;
    $help = $help.'<li>'.$entity_name. ' with ' .$bundle.' have <b>'.$count. '</b> elements </li>';
    $help = $help.'</ul>';
    $form['help']['#markup'] =   $help ;
    return  $form['help'];
}
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

      $form_state->setMethod('GET');
      $settings = $this->config('content_export_yaml.contentexportsetting');
      $export = \Drupal::service('content_export_yaml.manager');
      $query = $this->getRequest()->query->all();
      $path = $settings->get('path_export_content_folder');
      if(!$path){
        $form['help'] = [
          '#type' => 'item',
          '#markup' => 'Please fill the path settings  at first ',
          '#validated' => TRUE
        ];
      }
      $form['#prefix'] = '<div id="entity-wrapper">';
      $form['#suffix'] = '</div>';
      
      $entity_list_name = (\Drupal::entityTypeManager()->getDefinitions());
      $entity_list= array_merge(['' => '-- None --'],[]);
      foreach( $entity_list_name as $key => $entity_info){
        $entity_list[$key] =  $entity_info->getLabel();
      }
        $form['entity_name']  = [
          '#type' => 'select',
          '#title' => 'Select Entity Name',
          '#attributes' => ['name' => 'entity_name'],
          '#options' => $entity_list,
          '#validated' => TRUE,
           '#ajax' => [
          'callback' => [static::class, 'ajaxBundleCallback']   ,
          'method' => 'replace',        
          'wrapper' => ['entity-wrapper']
           ]
        ];
        $form['entity_name']['#disabled'] = FALSE;
        $bundle_list = ['' => 'none'];
        $form['bundle']  = [
          '#attributes' => ['name' => 'bundle'],
          '#type' => 'select',
          '#title' => 'Select Bundle name',
          '#validated' => TRUE,
          '#options' => $bundle_list,
          '#ajax' => [
            'callback' => [static::class, 'ajaxRangeCallback']   ,
            'method' => 'replace',        
            'wrapper' => ['entity-wrapper-help']
             ]
        ];
      if(!isset($query['ajax_form']) && isset($query['op'])
      && $query['op']=='Export'){
      $status_range = false ;
      if(isset($query['range']) && $query['range'] != ''){
        $range = trim($query['range']);
        $range_array = explode('-',$range);
        if(sizeof( $range_array ) == 2 && is_numeric($range_array[0]) 
        && is_numeric($range_array[1]) && $range_array[1] > 0 &&  $range_array[0] > 0  && $range_array[0] < $range_array[1]){
          $status_range = true ;
        }else{
          \Drupal::messenger()->addMessage(t('Range number is not correct format :'. $range ), 'error');
          return new RedirectResponse(Url::fromRoute('content_export_yaml.manage_content_yaml_form')->toString());   
        }
    
      }

      $config = $this->config('content_export_yaml.contentexportsetting');
      $root = $config->get('path_export_content_folder');
      $entity_name = $query['entity_name']; 
      $export = \Drupal::service('content_export_yaml.manager');
      if(!empty($status_range))
      {
        $results = $export->func->load_entity_list_range($entity_name,$query['bundle'], $range_array);
      } else{
        $results = $export->func->load_entity_list($entity_name, $query['bundle']);
      }
      $object = \Drupal::entityTypeManager()->getStorage($entity_name);
      $batch = [
        'title' => $this->t('Export Content  ...'),
        'operations' => [],
        'init_message' => $this->t('Starting ..'),
        'progress_message' => $this->t('Processd @current out of @total.'),
        'error_message' => $this->t('An error occurred during processing.'),
        'finished' => '\Drupal\content_export_yaml\Form\ContentExportManagerForm::exportFinishedCallback',
      ];
      if(empty($results)){
        \Drupal::messenger()->addMessage(t('Entity:'.$entity_name. ' and Bunlde:'.$query['bundle']. ' dont have content ' ), 'error');
        return new RedirectResponse(Url::fromRoute('content_export_yaml.manage_content_yaml_form')->toString()); 
      }
       foreach ($results as $key => $id){
                $batch['operations'][] = [$export->func->exportBase($id,$entity_name,  $root ), []];                
        }
          batch_set($batch);
        return batch_process(Url::fromRoute('content_export_yaml.manage_content_yaml_form')->toString());
            //return new RedirectResponse(Url::fromRoute('content_export_yaml.manage_content_yaml_form')->toString());   
      }
      $form['range'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Range number'),
          '#attributes' => ['name' => 'range'],
          '#default_value' => isset($query['range'])?$query['range']:'',
          '#description' => 'Leave empty to get all ,For example: 1 - 100'
      ];
      $form['help'] = [
        '#type' => 'item',
        '#title' => t('INFORMATION'),
        '#markup' => '',
        '#validated' => TRUE
      ];
      $form['help']['#prefix'] = '<div id="entity-wrapper-help">';
      $form['help']['#suffix'] = '</div>';
      $form['actions'] = ['#type' => 'actions'];
      $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => 'Export',

      ];
      $form['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => 'Reset',

    ];
  //  $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form_state->setCached(FALSE);
    return $form ;
  }
   protected function _getBundleName($result){
    $root_folder = dirname($result);
    return basename($root_folder);
   }
   protected function _getEntityType($result){
    $root_folder_1 = dirname($result);
    $root_folder = dirname($root_folder_1);
    return basename($root_folder);
   }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
   // $range = $query['entity_name'];
    //$form_state->setErrorByName('content_export_yaml', t("Please upload a correct file."));
  }

  /**
   *
   */
  protected function exportElement($result) {
    $config_name = basename($result,'.yml') ;
    $bundle = $this->_getBundleName($result) ;
    $entity_type = $this->_getEntityType($result);
    $export = \Drupal::service('content_export_yaml.manager');
  //  $export->import($config_name,$entity_type,$bundle); 
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
 
  }

   /**
   *
   */
  public static function exportFinishedCallback($success, $results, $operations) {
    if ($success) {
      $message = t('items successfully processed');
      \Drupal::messenger()->addMessage($message);
    }
    return new RedirectResponse(Url::fromRoute('content_export_yaml.manage_content_yaml_form')->toString());   
  }
}
