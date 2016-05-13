<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 20:46
 */

namespace Drupal\ctsearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ctsearch\Form\SearchForm;
use Drupal\ctsearch\SearchContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "ctsearch_search_results_block",
 *   admin_label = @Translation("CtSearch search results block"),
 *   category = @Translation("Search"),
 * )
 */
class SearchResultsBlock extends BlockBase implements ContainerFactoryPluginInterface
{


  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $storage = $container->get('entity.manager')->getStorage('block');
    /* @var $storage ConfigEntityStorage */
    //dsm($storage);
    return new static($configuration, $plugin_id, $plugin_definition);
  }


  public function build()
  {

    $config = $this->getConfiguration();

    $registry = theme_get_registry();

    $theme_hook_item = 'ctsearch_result_item';
    if(isset($config['theme_name']) && !empty($config['theme_name']) && isset($registry['ctsearch_result_item_' . $config['theme_name']])){
      $theme_hook_item = 'ctsearch_result_item_' . $config['theme_name'];
    }

    $theme_hook_list = 'ctsearch_result_list';
    if(isset($config['theme_name']) && !empty($config['theme_name']) && isset($registry['ctsearch_result_list_' . $config['theme_name']])){
      $theme_hook_list = 'ctsearch_result_list_' . $config['theme_name'];
    }

    $context = SearchContext::getInstance();

    if($context->getStatus() == SearchContext::CTSEARCH_STATUS_EXECUTED){
      if(isset($config['full_result_set']) && $config['full_result_set']){
        $context_new = clone $context;
        $context_new->setSize($context->getTotal());
        $context_new->refresh();
        $results = $context_new->getResults();
      }
      else{
        $results = $context->getResults();
      }
      $items = array();
      foreach($results as $result){
        $renderable = array(
          '#theme' => $theme_hook_item,
          '#item' => $result,
          '#cache' => array(
            'max-age' => 0,
          )
        );
        $items[] = render($renderable);
      }
      return array(
        '#theme' => $theme_hook_list,
        '#items' => $items,
        '#total' => $context->getTotal(),
        '#attached' => array(
          'library' => array('ctsearch/results'),
        ),
        '#cache' => array(
          'max-age' => 0,
        )
      );
    }
    else{
      return array(
        '#cache' => array(
          'max-age' => 0,
        )
      );
    }
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['theme_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Prefix theme name'),
      '#default_value' => isset($config['theme_name']) ? $config['theme_name'] : '',
      '#weight' => 9
    );
    $form['full_result_set'] = array(
      '#type' => 'checkbox',
      '#title' => t('Full result set?'),
      '#default_value' => isset($config['full_result_set']) ? $config['full_result_set'] : 0,
      '#weight' => 10
    );
    $form = $form + parent::buildConfigurationForm($form, $form_state);
    return $form;
  }
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['theme_name'] = $form_state->getValue('theme_name');
    $this->configuration['full_result_set'] = $form_state->getValue('full_result_set');
    parent::submitConfigurationForm($form, $form_state);
  }

}