<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 21:41
 */

namespace Drupal\ctsearch;


class SearchContext
{

  /**
   * @var string
   */
  private $query;

  /**
   * @var SearchContext
   */
  private static $instance = null;

  private function __construct()
  {
  }

  /**
   * @return SearchContext
   */
  public static function getInstance(){
    if(SearchContext::$instance == null) {
      dsm('I\'m instanciating my context');
      SearchContext::$instance = new static();
      SearchContext::$instance->build();
      if(SearchContext::$instance->isNotEmpty()){
        SearchContext::$instance->execute();
      }
    }
    dsm('I\'m returning my context instance');

    return SearchContext::$instance;
  }

  private function build(){
    dsm('I\'m building my context');
    $params = \Drupal::request()->query->all();
    if(isset($params['query'])){
      $this->query = trim($params['query']);
    }
  }

  /**
   * @return bool
   */
  private function isNotEmpty(){
    return isset($this->query) && !empty($this->query);
  }

  private function execute(){
    dsm('I\'m executing my context');
  }

}