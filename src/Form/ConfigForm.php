<?php

namespace Drupal\wingsuit_companion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm.
 */
class ConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wingsuit_companion_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wingsuit_companion.config');
    $form['dist_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dist path'),
      '#description' => $this->t('A local file system path to your themes dist directory.'),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => $config->get('dist_path'),
      '#weight' => '0',
    ];
    $form['only_own_layout'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use only Wingsuit patterns.'),
      '#description' => $this->t('Check this to hide all other layouts in layout builder.'),
      '#default_value' => $config->get('only_own_layout'),
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
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
    $this->configFactory()->getEditable('wingsuit_companion.config')
      ->set('dist_path', $form_state->getValue('dist_path'))
      ->set('only_own_layout', $form_state->getValue('only_own_layout'))
      ->save();
    $this->messenger()->addMessage('Wingsuit settings stored successfully.');
  }

}
