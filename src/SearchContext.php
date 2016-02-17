<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 21:41
 */

namespace Drupal\ctsearch;


use Drupal\Core\Url;

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
      SearchContext::$instance = new static();
      SearchContext::$instance->build();
      if(SearchContext::$instance->isNotEmpty()){
        SearchContext::$instance->execute();
      }
    }

    return SearchContext::$instance;
  }

  private function build(){
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
    $ctsearch_url = \Drupal::config('ctsearch.settings')->get('ctsearch_url');
    $params = array(
      'mapping' => \Drupal::config('ctsearch.settings')->get('mapping'),
    );
    if(isset($this->query) && !empty($this->query)){
      $params['query'] = $this->query;
    }
    $url = Url::fromUri($ctsearch_url, array('absolute' => true, 'query' => $params));
    dsm($url->toString());
  }

}