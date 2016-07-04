<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 20:46
 */

namespace Drupal\ctsearch\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\ctsearch\Form\AdvancedSearchForm;

/**
 * @Block(
 *   id = "ctsearch_advanced_search_block",
 *   admin_label = @Translation("CtSearch advanced search block"),
 *   category = @Translation("Search"),
 * )
 */
class AdvancedSearchBlock extends BlockBase
{

  public function build()
  {
    $form = \Drupal::formBuilder()->getForm(AdvancedSearchForm::class);
    $form['#attached']['library'] = array('ctsearch/advanced_search');
    return $form;
  }

}