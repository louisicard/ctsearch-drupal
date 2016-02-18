<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 20:46
 */

namespace Drupal\ctsearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\ctsearch\Form\SearchForm;
use Drupal\ctsearch\SearchContext;

/**
 * @Block(
 *   id = "ctsearch_search_results_block",
 *   admin_label = @Translation("CtSearch search results block"),
 *   category = @Translation("Search"),
 * )
 */
class SearchResultsBlock extends BlockBase
{

  public function build()
  {
    $registry = theme_get_registry();
    $theme_hook = 'ctsearch_result_item';
    if(isset($registry['ctsearch_result_item_override'])){
      $theme_hook = 'ctsearch_result_item_override';
    }

    $context = SearchContext::getInstance();

    if($context->getStatus() == SearchContext::CTSEARCH_STATUS_EXECUTED){
      $items = array();
      foreach($context->getResults() as $result){
        $renderable = array(
          '#theme' => $theme_hook,
          '#item' => $result,
          '#cache' => array(
            'max-age' => 0,
          )
        );
        $items[] = render($renderable);
      }
      return array(
        '#theme' => 'ctsearch_result_list',
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

}