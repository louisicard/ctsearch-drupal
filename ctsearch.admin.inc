<?php

function ctsearch_settings_admin_form($form, &$form_state) {

  $form['ctsearch_url'] = array(
    '#type' => 'textfield',
    '#required' => true,
    '#title' => t('CtSearch URL'),
    '#description' => t('The URL of CtSearch Search Service'),
    '#default_value' => variable_get('ctsearch_url', '')
  );

  $form['ctsearch_mapping'] = array(
    '#type' => 'textfield',
    '#required' => true,
    '#title' => t('Mapping'),
    '#description' => t('Mapping to search for (E.g. index_name.mapping_name)'),
    '#default_value' => variable_get('ctsearch_mapping', '')
  );

  $form['ctsearch_search_analyzer'] = array(
    '#type' => 'textfield',
    '#required' => false,
    '#title' => t('Search analyzer'),
    '#description' => t('Anlyzer to use for the search query'),
    '#default_value' => variable_get('ctsearch_search_analyzer', '')
  );

  $form['ctsearch_facets'] = array(
    '#type' => 'textfield',
    '#required' => true,
    '#title' => t('Facets'),
    '#description' => t('List of fields to build facets (comma separated)'),
    '#default_value' => variable_get('ctsearch_facets', ''),
    '#maxlength' => 255,
  );

  $form['ctsearch_sort_fields'] = array(
    '#type' => 'textfield',
    '#required' => true,
    '#title' => t('Sortable fields'),
    '#description' => t('List of fields to sort on (comma separated). E.g.: _score|Relevance,date_index|Date of index'),
    '#default_value' => variable_get('ctsearch_sort_fields', ''),
    '#maxlength' => 255,
  );

  $form['ctsearch_highlighted_fields'] = array(
    '#type' => 'textfield',
    '#required' => true,
    '#title' => t('Highlighted fields'),
    '#description' => t('List of fields to highlight with options (field_name|fragment_size|number_of_fragments|no_match_size). E.g.: title|100|5|99999,body|100|2|0'),
    '#default_value' => variable_get('ctsearch_highlighted_fields', ''),
    '#maxlength' => 255,
  );

  $form['ctsearch_advanced_search_fields'] = array(
    '#type' => 'textfield',
    '#required' => true,
    '#title' => t('Advanced search fields'),
    '#description' => t('List of fields to enable for advanced search purposes (field_name|label). E.g.: title|Title'),
    '#default_value' => variable_get('ctsearch_advanced_search_fields', ''),
    '#maxlength' => 255,
  );

  $form['ctsearch_suggest_fields'] = array(
    '#type' => 'textfield',
    '#required' => false,
    '#title' => t('Suggestion fields'),
    '#description' => t('List of fields to use for suggestions on empty results. E.g.: title,body'),
    '#default_value' => variable_get('ctsearch_suggest_fields', ''),
    '#maxlength' => 255,
  );

  $form['ctsearch_search_page_uri'] = array(
    '#type' => 'textfield',
    '#required' => false,
    '#title' => t('Search page URI'),
    '#description' => t('URI of the the page where the search results should be rendered'),
    '#default_value' => variable_get('ctsearch_search_page_uri', '')
  );

  $form['ctsearch_index'] = array(
    '#title' => t('Indexing settings'),
    '#type' => 'fieldset',
  );

  $form['ctsearch_index']['ctsearch_autoindex'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable auto indexing'),
    '#default_value' => variable_get('ctsearch_autoindex', false),
  );

  $form['ctsearch_index']['ctsearch_index_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Index service URL'),
    '#default_value' => variable_get('ctsearch_index_url', ''),
  );

  $form['ctsearch_index']['ctsearch_datasource_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Datasource ID'),
    '#default_value' => variable_get('ctsearch_datasource_id', ''),
  );

  $form['ctsearch_index']['ctsearch_target_mapping'] = array(
    '#type' => 'textfield',
    '#title' => t('Target mapping'),
    '#default_value' => variable_get('ctsearch_target_mapping', ''),
  );

  return system_settings_form($form);
}

function ctsearch_reindex_admin_form($form, &$form_state){

  $form['#prefix'] = '<p>' . t('Reindex all nodes') . '</p>';

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Reindex')
  );
  return $form;
}
function ctsearch_reindex_admin_form_submit($form, &$form_state){
  $batch = array(
    'title' => t('Reindexing'),
    'operations' => array(
      array('ctsearch_reindex_all_nodes', array()),
    ),
    'finished' => 'ctsearch_reindex_all_nodes_callback',
    'file' => drupal_get_path('module', 'ctsearch') . '/ctsearch.inc',
  );
  batch_set($batch);
}

function ctsearch_cleanindex_admin_form($form, &$form_state){

  $form['#prefix'] = '<p>' . t('Clean index') . '</p>';

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Clean index')
  );
  return $form;
}
function ctsearch_cleanindex_admin_form_submit($form, &$form_state){
  $batch = array(
    'title' => t('Cleaning index'),
    'operations' => array(
      array('ctsearch_cleanindex_all_nodes', array()),
    ),
    'finished' => 'ctsearch_cleanindex_all_nodes_callback',
    'file' => drupal_get_path('module', 'ctsearch') . '/ctsearch.inc',
  );
  batch_set($batch);
}