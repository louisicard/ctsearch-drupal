<?php

function ctsearch_reindex_all_nodes(&$context) {
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = db_query('SELECT COUNT(DISTINCT nid) FROM {node}')->fetchField();
  }
  $limit = 5;
  $query = new EntityFieldQuery();
  $result = $query->entityCondition('entity_type', 'node')
    ->propertyCondition('nid', $context['sandbox']['current_node'], '>')
    ->propertyOrderBy('nid', 'ASC')
    ->range(0, $limit)
    ->execute();
  if(isset($result['node'])) {
    foreach (array_keys($result['node']) as $nid) {
      $node = node_load($nid);
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $node->nid;
      if (ExportController::handleEntityUpdate($node)) {
        $context['message'] = t('Indexing @node_title', array('@node_title' => $node->title));
        $context['results'][] = $node->nid . ' : ' . $node->title;
      }
    }
  }
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

function ctsearch_reindex_all_nodes_callback($success, $results, $operations) {
  if ($success) {
    $message = t('@count node(s) indexed.', array('@count' => count($results)));
  } else {
    $message = t('Finished with an error.');
  }
  drupal_set_message($message);
}

function ctsearch_cleanindex_all_nodes(&$context) {
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = db_query('SELECT COUNT(DISTINCT nid) FROM {node}')->fetchField();
  }
  $limit = 5;
  $query = new EntityFieldQuery();
  $result = $query->entityCondition('entity_type', 'node')
    ->propertyCondition('nid', $context['sandbox']['current_node'], '>')
    ->propertyOrderBy('nid', 'ASC')
    ->range(0, $limit)
    ->execute();
  if(isset($result['node'])) {
    foreach (array_keys($result['node']) as $nid) {
      $node = node_load($nid);
      $context['sandbox']['progress']++;
      $context['sandbox']['current_node'] = $node->nid;
      if (ExportController::handleEntityDelete($node)) {
        $context['message'] = t('Removing @node_title', array('@node_title' => $node->title));
        $context['results'][] = $node->nid . ' : ' . $node->title;
      }
    }
  }
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

function ctsearch_cleanindex_all_nodes_callback($success, $results, $operations) {
  if ($success) {
    $message = t('@count node(s) removed.', array('@count' => count($results)));
  } else {
    $message = t('Finished with an error.');
  }
  drupal_set_message($message);
}