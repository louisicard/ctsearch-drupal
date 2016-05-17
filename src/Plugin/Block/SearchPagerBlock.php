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
 *   id = "ctsearch_search_pager_block",
 *   admin_label = @Translation("CtSearch search pager block"),
 *   category = @Translation("Search"),
 * )
 */
class SearchPagerBlock extends BlockBase
{

  public function build()
  {

    $context = SearchContext::getInstance();

    if($context->getStatus() == SearchContext::CTSEARCH_STATUS_EXECUTED && $context->getTotal() > $context->getSize()){
      return array(
        '#markup' => $this->getPagerMarkup($context),
        '#attached' => array(
          'library' => array('ctsearch/pager'),
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

  /**
   * @param SearchContext $searchContext
   */
  private function getPagerMarkup($searchContext){

    $html = '<ul class="ctsearch-pager clearfix">';

    $currentPage = $searchContext->getFrom() / $searchContext->getSize() + 1;
    $previousPage = $currentPage > 1 ? $currentPage - 1 : null;
    $nextPage = ($currentPage + 1) <= ceil($searchContext->getTotal() / $searchContext->getSize()) ? $currentPage + 1 : null;

    if($previousPage != null){
      $html .= '<li class="page prev"><a href="' . $searchContext->getPagedUrl(($previousPage - 1) * $searchContext->getSize())->toString() . '">' . t('Prev') . '</a></li>';
    }
    $nbPages = 0;
    $i = $currentPage;
    $pages = '';
    while($nbPages <= 3 && $i > 0){
      $pages = '<li class="page' . ($i == $currentPage ? ' active' : '') . '"><a href="' . $searchContext->getPagedUrl(($i - 1) * $searchContext->getSize())->toString() . '">' . $i . '</a></li>' . $pages;
      $nbPages++;
      $i--;
    }
    $i = $currentPage + 1;
    while($nbPages < 6 && $i <= ceil($searchContext->getTotal() / $searchContext->getSize())){
      $pages .= '<li class="page"><a href="' . $searchContext->getPagedUrl(($i - 1) * $searchContext->getSize())->toString() . '">' . $i . '</a></li>';
      $nbPages++;
      $i++;
    }
    $html .= $pages;
    if($nextPage != null){
      $html .= '<li class="page next"><a href="' . $searchContext->getPagedUrl(($nextPage - 1) * $searchContext->getSize())->toString() . '">' . t('Next') . '</a></li>';
    }
    $html .= '</ul>';
    return $html;
  }

}