<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 21:22
 */

namespace Drupal\ctsearch\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctsearch\SearchContext;

class SearchForm extends FormBase
{
  public function getFormId()
  {
    return 'ctsearch_search';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $context = SearchContext::getInstance();
    $form['query'] = array(
      '#type' => 'textfield',
      '#required' => true,
      '#title' => t('Search'),
      '#default_value' => $context->getQuery() != null ? $context->getQuery() : '',
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search')
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRedirect('<current>', array(), array('query' => array('query' => $form_state->getValue('query'))));
  }

}