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

    return array(
      '#markup' => 'My name is ' . $facet_id,
      '#cache' => array(
        'max-age' => 0,
      )
    );
  }

}