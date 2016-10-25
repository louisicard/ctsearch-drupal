<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 25/10/2016
 * Time: 15:38
 */

namespace Drupal\ctsearch\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ReindexForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ctsearch_reindex_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<p>' . t('Reindex all nodes') . '</p>';

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Reindex')
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
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

}
