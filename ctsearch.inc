<?php

function ctsearch_reindex_all_nodes(&$context) {
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = \Drupal::database()->query('SELECT COUNT(DISTINCT nid) FROM {node}')->fetchField();
  }
  $limit = 5;
  $result = \Drupal::entityQuery('node')
    ->condition('nid', $context['sandbox']['current_node'], '>')
    ->sort('nid', 'ASC')
    ->range(0, $limit)
    ->execute();
  foreach ($result as $vid => $nid) {
    $node = Drupal\node\Entity\Node::load($nid);
    $context['sandbox']['progress'] ++;
    $context['sandbox']['current_node'] = $node->id();
    if (\Drupal\ctsearch\Controller\ExportController::handleEntityUpdate($node)) {
      $context['message'] = t('Indexing @node_title', array('@node_title' => $node->get('title')->value));
      $context['results'][] = $node->id() . ' : ' . $node->get('title')->value;
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
    $context['sandbox']['max'] = \Drupal::database()->query('SELECT COUNT(DISTINCT nid) FROM {node}')->fetchField();
  }
  $limit = 5;
  $result = \Drupal::entityQuery('node')
    ->condition('nid', $context['sandbox']['current_node'], '>')
    ->sort('nid', 'ASC')
    ->range(0, $limit)
    ->execute();
  foreach ($result as $vid => $nid) {
    $node = Drupal\node\Entity\Node::load($nid);
    $context['sandbox']['progress'] ++;
    $context['sandbox']['current_node'] = $node->id();
    if (\Drupal\ctsearch\Controller\ExportController::handleEntityDelete($node)) {
      $context['message'] = t('Removing @node_title', array('@node_title' => $node->get('title')->value));
      $context['results'][] = $node->id() . ' : ' . $node->get('title')->value;
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