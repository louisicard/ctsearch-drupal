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

/**
 * @Block(
 *   id = "ctsearch_search_block",
 *   admin_label = @Translation("CtSearch search block"),
 *   category = @Translation("Search"),
 * )
 */
class SearchBlock extends BlockBase
{

  public function build()
  {
    $form = \Drupal::formBuilder()->getForm(SearchForm::class);
    return $form;
  }

}