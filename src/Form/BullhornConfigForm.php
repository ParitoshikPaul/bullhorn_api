<?php

namespace Drupal\bullhorn_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BullhornConfigForm.
 */
class BullhornConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bullhorn_api.bullhornconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bullhorn_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bullhorn_api.bullhornconfig');
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('The unique OAuth ID string given to you for the Bullhorn REST API.'),
      '#default_value' => $config->get('client_id'),
      '#required' => true
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('The secret OAuth key given to you for the Bullhorn REST API.'),
      '#default_value' => $config->get('client_secret'),
      '#required' => true
    ];
    $form['bullhorn_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bullhorn Username'),
      '#description' => $this->t('Bullhorn client username.'),
      '#default_value' => $config->get('bullhorn_username'),
      '#required' => true
    ];
    $form['bullhorn_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bullhorn Password'),
      '#description' => $this->t('Bullhorn client password.'),
      '#default_value' => $config->get('bullhorn_password'),
      '#required' => true
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('bullhorn_api.bullhornconfig')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->set('bullhorn_username', $form_state->getValue('bullhorn_username'))
      ->set('bullhorn_password', $form_state->getValue('bullhorn_password'))
      ->save();
     
  }


}
        
  /**
   * Gets authorization code.
   */
  function bullhorn_get_access_token() {
    $config = \Drupal::config('bullhorn_api.bullhornconfig');
    $client_id = $config->get('client_id');
    $client_secret = $config->get('client_secret');
    $bullhorn_username = $config->get('bullhorn_username');
    $bullhorn_password = $config->get('bullhorn_password');
    $ch = curl_init(BULLHORN_OAUTH_ENDPOINT . 'authorize');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id=' . $client_id . '&response_type=code&redirect_uri=' . BULLHORN_REDIRECTURI . '&state=' . BULLHORN_STATE . '&action=Login&username=' . $bullhorn_username . '&password=' . $bullhorn_password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $rs = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/code=([^&]+)/', $rs, $r)) {
      
      $ch = curl_init(BULLHORN_OAUTH_ENDPOINT . 'token');

      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=authorization_code&code=' . $r[1] . '&client_id=' .$client_id . '&client_secret=' . $client_secret . "&redirect_uri=" . BULLHORN_REDIRECTURI);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
      $rs = curl_exec($ch);
      curl_close($ch);
      $json_rs = json_decode($rs);
      return $json_rs->access_token;
    }
    else {
      return;
    }
  }

  function bullhorn_get_BhRestToken(){

    $access_token = bullhorn_get_access_token();


    $ch = curl_init(BULLHORN_OAUTH_ENDPOINT . 'login');

    curl_setopt($ch, CURLOPT_GET, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'version=*&access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  
    $rs = curl_exec($ch);
    curl_close($ch);
    print_r($rs);die;
    $json_rs = json_decode($rs);
    //return $json_rs->access_token;
    
  }


  function bullhorn_get_candidate(){

    $access_token = bullhorn_get_access_token();
    
  }
  bullhorn_get_BhRestToken();