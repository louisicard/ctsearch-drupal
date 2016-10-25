<?php

namespace Drupal\ctsearch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CleanIndexForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ctsearch_cleanindex_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<p>' . t('Remove all nodes from index') . '</p>';

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Clean index')
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = array(
      'title' => t('Unindexing'),
      'operations' => array(
        array('ctsearch_cleanindex_all_nodes', array()),
      ),
      'finished' => 'ctsearch_cleanindex_all_nodes_callback',
      'file' => drupal_get_path('module', 'ctsearch') . '/ctsearch.inc',
    );
    batch_set($batch);
  }

}
