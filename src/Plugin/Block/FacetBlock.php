<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 20:46
 */

namespace Drupal\ctsearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\ctsearch\SearchContext;

/**
 * @Block(
 *   id = "ctsearch_facet_block",
 *   admin_label = @Translation("CtSearch facet block"),
 *   category = @Translation("Search"),
 *   deriver = "Drupal\ctsearch\Plugin\Derivative\FacetBlock"
 * )
 */
class FacetBlock extends BlockBase
{

  public function build()
  {
    $facet_id = $this->getDerivativeId();

    $context = SearchContext::getInstance();

    if($context->getStatus() == SearchContext::CTSEARCH_STATUS_EXECUTED){
      if(isset($context->getFacets()[$facet_id]) && isset($context->getFacets()[$facet_id]['buckets']) && count($context->getFacets()[$facet_id]['buckets']) > 0){
        $facet = $context->getFacets()[$facet_id];
        if(isset($facet['buckets'])){
          foreach($facet['buckets'] as $index => $bucket){
            $facet['buckets'][$index]['filter_url'] = $context->buildFilterUrl($facet_id, $bucket['key']);
            if($context->isFilterApplied($facet_id, $bucket['key'])){
              $facet['buckets'][$index]['remove_filter_url'] = $context->buildFilterRemovalUrl($facet_id, $bucket['key']);
            }
          }
        }
        if(isset($facet['sum_other_doc_count']) && $facet['sum_other_doc_count'] > 0){
          $facet['see_more_url'] = $context->getFacetRaiseSizeUrl($facet_id);
        }
        return array(
          '#theme' => 'facet_block',
          '#facet_id' => $facet_id,
          '#facet' => $facet,
          '#attached' => array(
            'library' => array('ctsearch/facets'),
          ),
          '#cache' => array(
            'max-age' => 0,
          )
        );
      }
    }
    return array(
      '#cache' => array(
        'max-age' => 0,
      )
    );
  }

}