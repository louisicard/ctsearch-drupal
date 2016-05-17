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
 *   id = "ctsearch_sort_block",
 *   admin_label = @Translation("CtSearch sort block"),
 *   category = @Translation("Search"),
 * )
 */
class SortBlock extends BlockBase
{

  public function build()
  {
    $context = SearchContext::getInstance();
    if($context->getStatus() == SearchContext::CTSEARCH_STATUS_EXECUTED){
      $fields = \Drupal::config('ctsearch.settings')->get('sort_fields');
      $sortable = array();
      foreach(explode(',', $fields) as $field){
        $field_r = explode('|', trim($field));
        if(count($field_r) == 2) {
          if (trim($field_r[0]) != '_score') {
            $sortable[trim($field_r[1]) . ' (asc)'] = array(
              'key' => trim($field_r[0]) . ',asc',
              'active' => $context->getSort() == trim($field_r[0]) . ',asc',
              'link' => $context->getPagedUrl(null, trim($field_r[0]) . ',asc')
            );
            $sortable[trim($field_r[1]) . ' (desc)'] = array(
              'key' => trim($field_r[0]) . ',desc',
              'active' => $context->getSort() == trim($field_r[0]) . ',desc',
              'link' => $context->getPagedUrl(null, trim($field_r[0]) . ',desc')
            );
          } else {
            $sortable[trim($field_r[1])] = array(
              'key' => trim($field_r[0]) . ',desc',
              'active' => $context->getSort() == '_score,desc',
              'link' => $context->getPagedUrl(null, '_score,desc')
            );
          }
        }
      }
      return array(
        '#theme' => 'sort_block',
        '#sortable' => $sortable,
        '#cache' => array(
          'max-age' => 0,
        )
      );
    }
    return array(
      '#cache' => array(
        'max-age' => 0,
      )
    );
  }

}