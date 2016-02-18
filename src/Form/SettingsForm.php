<?php

namespace Drupal\ctsearch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {
  
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ctsearch_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ctsearch.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ctsearch.settings');

    $form['ctsearch_url'] = array(
      '#type' => 'textfield',
      '#required' => true,
      '#title' => t('CtSearch URL'),
      '#description' => t('The URL of CtSearch Search Service'),
      '#default_value' => $config->get('ctsearch_url')
    );

    $form['mapping'] = array(
      '#type' => 'textfield',
      '#required' => true,
      '#title' => t('Mapping'),
      '#description' => t('Mapping to search for (E.g. index_name.mapping_name)'),
      '#default_value' => $config->get('mapping')
    );

    $form['search_analyzer'] = array(
      '#type' => 'textfield',
      '#required' => false,
      '#title' => t('Search analyzer'),
      '#description' => t('Anlyzer to use for the search query'),
      '#default_value' => $config->get('search_analyzer')
    );

    $form['facets'] = array(
      '#type' => 'textfield',
      '#required' => true,
      '#title' => t('Facets'),
      '#description' => t('List of fields to build facets (comma separated)'),
      '#default_value' => $config->get('facets')
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ctsearch.settings')
      ->set('ctsearch_url', $form_state->getValue('ctsearch_url'))
      ->set('mapping', $form_state->getValue('mapping'))
      ->set('search_analyzer', $form_state->getValue('search_analyzer'))
      ->set('facets', $form_state->getValue('facets'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  
}

