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
      '#default_value' => $config->get('facets'),
      '#maxlength' => 255,
    );

    $form['sort_fields'] = array(
      '#type' => 'textfield',
      '#required' => true,
      '#title' => t('Sortable fields'),
      '#description' => t('List of fields to sort on (comma separated). E.g.: _score|Relevance,date_index|Date of index'),
      '#default_value' => $config->get('sort_fields'),
      '#maxlength' => 255,
    );

    $form['search_page_uri'] = array(
      '#type' => 'textfield',
      '#required' => false,
      '#title' => t('Search page URI'),
      '#description' => t('URI of the the page where the search results should be rendered'),
      '#default_value' => $config->get('search_page_uri')
    );

    $form['ctsearch_index'] = array(
      '#title' => t('Indexing settings'),
      '#type' => 'details',
      '#open' => true,
    );

    $form['ctsearch_index']['ctsearch_autoindex'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable auto indexing'),
      '#default_value' => $config->get('ctsearch_autoindex'),
    );

    $form['ctsearch_index']['ctsearch_index_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Index service URL'),
      '#default_value' => $config->get('ctsearch_index_url'),
    );

    $form['ctsearch_index']['ctsearch_datasource_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Datasource ID'),
      '#default_value' => $config->get('ctsearch_datasource_id'),
    );

    $form['ctsearch_index']['ctsearch_target_mapping'] = array(
      '#type' => 'textfield',
      '#title' => t('Target mapping'),
      '#default_value' => $config->get('ctsearch_target_mapping'),
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
      ->set('sort_fields', $form_state->getValue('sort_fields'))
      ->set('search_page_uri', $form_state->getValue('search_page_uri'))
      ->set('ctsearch_autoindex', $form_state->getValue('ctsearch_autoindex'))
      ->set('ctsearch_index_url', $form_state->getValue('ctsearch_index_url'))
      ->set('ctsearch_datasource_id', $form_state->getValue('ctsearch_datasource_id'))
      ->set('ctsearch_target_mapping', $form_state->getValue('ctsearch_target_mapping'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  
}

