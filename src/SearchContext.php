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

  const CTSEARCH_STATUS_IDLE = 1;
  const CTSEARCH_STATUS_EXECUTED = 2;

  /**
   * @var string
   */
  private $query;

  /**
   * @var array
   */
  private $filters = array();

  /**
   * @var array
   */
  private $facetOptions = array();

  /**
   * @var array
   */
  private $results = array();

  /**
   * @var int
   */
  private $total = 0;

  /**
   * @var int
   */
  private $size = 10;

  /**
   * @var int
   */
  private $from = 0;

  /**
   * @var string
   */
  private $sort = '_score,desc';

  /**
   * @var array
   */
  private $facets = array();
  /**
   * @var int
   */
  private $status = SearchContext::CTSEARCH_STATUS_IDLE;

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
  public static function getInstance($noBuild = FALSE){
    if(SearchContext::$instance == null) {
      SearchContext::$instance = new static();
      if(!$noBuild) {
        SearchContext::$instance->build();
        if (SearchContext::$instance->isNotEmpty()) {
          SearchContext::$instance->execute();
        }
      }
    }

    return SearchContext::$instance;
  }

  public function refresh(){
    if ($this->isNotEmpty()) {
      $this->execute();
    }
  }

  public function getDocumentById($id){
    $ctsearch_url = \Drupal::config('ctsearch.settings')->get('ctsearch_url');
    $params = array(
      'mapping' => \Drupal::config('ctsearch.settings')->get('mapping'),
      'doc_id' => $id,
    );
    $url = Url::fromUri($ctsearch_url, array('absolute' => true, 'query' => $params));
    $response = $this->getResponse($url->toString());
    if(isset($response['hits']['hits'][0])){
      return $response['hits']['hits'][0];
    }
    return null;
  }

  private function build(){
    $params = \Drupal::request()->query->all();
    if(isset($params['query'])){
      $this->query = trim($params['query']);
    }
    if(isset($params['filter'])){
      foreach($params['filter'] as $filter){
        $this->filters[] = $filter;
      }
    }
    if(isset($params['facetOptions'])){
      foreach($params['facetOptions'] as $option){
        $option_def = explode(',', $option);
        if(count($option_def) == 3){
          $this->facetOptions[$option_def[0]][$option_def[1]] = $option_def[2];
        }
      }
    }
    if(isset($params['size'])){
      $this->size = $params['size'];
    }
    if(isset($params['from'])){
      $this->from = $params['from'];
    }
    if(isset($params['sort'])){
      $this->sort = $params['sort'];
    }
  }

  /**
   * @return bool
   */
  private function isNotEmpty(){
    return isset($this->query) && !empty($this->query) || !empty($this->filters);
  }

  private function execute(){
    $ctsearch_url = \Drupal::config('ctsearch.settings')->get('ctsearch_url');
    $params = array(
      'mapping' => \Drupal::config('ctsearch.settings')->get('mapping'),
      'facets' => \Drupal::config('ctsearch.settings')->get('facets'),
    );
    if(\Drupal::config('ctsearch.settings')->get('search_analyzer') != null && !empty(\Drupal::config('ctsearch.settings')->get('search_analyzer'))){
      $params['analyzer'] = \Drupal::config('ctsearch.settings')->get('search_analyzer');
    }
    if(isset($this->query) && !empty($this->query)){
      $params['query'] = $this->query;
    }
    if(!empty($this->filters)){
      $params['filter'] = $this->filters;
    }
    if(!empty($this->facetOptions)){
      foreach($this->facetOptions as $facet_id => $options) {
        foreach($options as $k => $v){
          $params['facetOptions'][] = $facet_id . ',' . $k . ',' . $v;
        }
      }
    }
    $params['size'] = $this->size;
    $params['from'] = $this->from;
    $params['sort'] = $this->sort;
    if(!empty(\Drupal::config('ctsearch.settings')->get('highlighted_fields'))){
      $params['highlights'] = \Drupal::config('ctsearch.settings')->get('highlighted_fields');
    }
    $url = Url::fromUri($ctsearch_url, array('absolute' => true, 'query' => $params));
    $response = $this->getResponse($url->toString());
    if(isset($response['hits']['hits'])){
      $this->results = $response['hits']['hits'];
    }
    if(isset($response['hits']['total'])){
      $this->total = $response['hits']['total'];
    }
    if(isset($response['aggregations'])){
      $this->facets = $response['aggregations'];
    }
    $this->status = SearchContext::CTSEARCH_STATUS_EXECUTED;
  }

  private function getResponse($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($code == 200) {
      return json_decode($r, true);
    }
    else{
      throw new \Exception("CtSearch response failed => code " . $code);
    }
  }

  public function buildFilterUrl($field, $value){
    $params = \Drupal::request()->query->all();
    unset($params['facetOptions']);
    $params['filter'][] = $field . '="' . $value . '"';
    return Url::fromRoute('<current>', array(), array('absolute' => true, 'query' => $params));
  }

  public function buildFilterRemovalUrl($field, $value){
    $params = \Drupal::request()->query->all();
    unset($params['filter']);
    unset($params['facetOptions']);
    foreach($this->filters as $filter) {
      if($filter != $field . '="' . $value . '"') {
        $params['filter'][] = $filter;
      }
    }
    return Url::fromRoute('<current>', array(), array('absolute' => true, 'query' => $params));
  }

  public function isFilterApplied($field, $value){
    return in_array($field . '="' . $value . '"', $this->filters);
  }

  public function getFacetRaiseSizeUrl($facet_id){
    $pace = 10;
    $new_size = isset($this->facetOptions[$facet_id]['size']) ? $this->facetOptions[$facet_id]['size'] + $pace : $pace * 2;
    $this->facetOptions[$facet_id]['size'] = $new_size;

    $params = \Drupal::request()->query->all();
    if(isset($params['facetOptions'])) {
      unset($params['facetOptions']);
    }
    foreach($this->facetOptions as $facet_id => $options) {
      foreach($options as $k => $v){
        $params['facetOptions'][] = $facet_id . ',' . $k . ',' . $v;
      }
    }
    return Url::fromRoute('<current>', array(), array('absolute' => true, 'query' => $params));
  }

  public function getPagedUrl($from = null, $sort = null){
    $params = \Drupal::request()->query->all();
    if($from != null) {
      $params['from'] = $from;
    }
    if($sort != null) {
      $params['sort'] = $sort;
    }
    return Url::fromRoute('<current>', array(), array('absolute' => true, 'query' => $params));
  }

  /**
   * @return string
   */
  public function getQuery()
  {
    return isset($this->query) ? $this->query : null;
  }

  /**
   * @param string $query
   */
  public function setQuery($query)
  {
    $this->query = $query;
  }

  /**
   * @return array
   */
  public function getResults()
  {
    return $this->results;
  }

  /**
   * @param array $results
   */
  public function setResults($results)
  {
    $this->results = $results;
  }

  /**
   * @return array
   */
  public function getFacets()
  {
    return $this->facets;
  }

  /**
   * @param array $facets
   */
  public function setFacets($facets)
  {
    $this->facets = $facets;
  }

  /**
   * @return int
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * @param int $status
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }

  /**
   * @return array
   */
  public function getFilters()
  {
    return $this->filters;
  }

  /**
   * @return int
   */
  public function getTotal()
  {
    return $this->total;
  }

  /**
   * @param int $total
   */
  public function setTotal($total)
  {
    $this->total = $total;
  }

  /**
   * @return int
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * @param int $size
   */
  public function setSize($size)
  {
    $this->size = $size;
  }

  /**
   * @return int
   */
  public function getFrom()
  {
    return $this->from;
  }

  /**
   * @param int $from
   */
  public function setFrom($from)
  {
    $this->from = $from;
  }

  /**
   * @return string
   */
  public function getSort()
  {
    return $this->sort;
  }

  /**
   * @param string $sort
   */
  public function setSort($sort)
  {
    $this->sort = $sort;
  }

}