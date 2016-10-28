<?php

function ctsearch_search_form($form, &$form_state){
  $context = SearchContext::getInstance();
  $form['query'] = array(
    '#type' => 'textfield',
    '#required' => false,
    '#title' => t('Search'),
    '#default_value' => $context->getQuery() != null ? $context->getQuery() : '',
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Search')
  );
  return $form;
}
function ctsearch_search_form_submit(&$form, &$form_state){
  if(variable_get('ctsearch_search_page_uri', null) != null && variable_get('ctsearch_search_page_uri', null) != ''){
    try {
      $form_state['redirect'] = url(variable_get('ctsearch_search_page_uri', null), array('absolute' => true, 'query' => array('query' => !empty($form_state['values']['query']) ? $form_state['values']['query'] : '*')));
    }
    catch(Exception $ex){
      drupal_set_message('Search page URL is incorrect : ' . $ex->getMessage(), 'error');
      $form_state['redirect'] = url(current_path(), array('absolute' => true, 'query' => array('query' => !empty($form_state['values']['query']) ? $form_state['values']['query'] : '*')));
    }
  }
  else {
    $form_state['redirect'] = url(current_path(), array('absolute' => true, 'query' => array('query' => !empty($form_state['values']['query']) ? $form_state['values']['query'] : '*')));
  }
}
function ctsearch_advanced_search_form($form, &$form_state){
  $context = SearchContext::getInstance();
  $form['query'] = array(
    '#type' => 'textfield',
    '#required' => false,
    '#title' => t('Search'),
    '#default_value' => $context->getQuery() != null ? $context->getQuery() : '',
  );
  $form['advanced_search_fields'] = array(
    '#type' => 'hidden',
    '#default_value' => variable_get('ctsearch_advanced_search_fields', ''),
  );
  $form['advanced_query_json'] = array(
    '#type' => 'hidden',
    '#default_value' => json_encode($context->getAdvancedFilters()),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Search')
  );

  return $form;
}
function ctsearch_advanced_search_form_submit(&$form, &$form_state){
  $adv = json_decode($form_state['values']['advanced_query_json'], true);
  $advFilters = [];
  if(count($adv) > 0){
    foreach($adv as $item) {
      $advFilters[] = $item["field"] . '="' . $item['value'] . '"';
    }
  }
  if(variable_get('ctsearch_search_page_uri', null) != null && variable_get('ctsearch_search_page_uri', null) != ''){
    try {
      $form_state['redirect'] = url(variable_get('ctsearch_search_page_uri', null), array('absolute' => true, 'query' => array('query' => !empty($form_state['values']['query']) ? $form_state['values']['query'] : '*', 'qs_filter' => $advFilters)));
    }
    catch(\Exception $ex){
      drupal_set_message('Search page URL is incorrect : ' . $ex->getMessage(), 'error');
      $form_state['redirect'] = url(current_path(), array('absolute' => true, 'query' => array('query' => !empty($form_state['values']['query']) ? $form_state['values']['query'] : '*', 'qs_filter' => $advFilters)));
    }
  }
  else {
    $form_state['redirect'] = url(current_path(), array('absolute' => true, 'query' => array('query' => !empty($form_state['values']['query']) ? $form_state['values']['query'] : '*', 'qs_filter' => $advFilters)));
  }
}