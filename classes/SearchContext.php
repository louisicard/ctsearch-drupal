<?php

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
  private $advancedFilters = array();

  /**
   * @var array
   */
  private $ids = array();

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
   * @var string
   */
  private $currentRequestUrl = "";

  /**
   * @var string
   */
  private $didYouMean = null;

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

  public static function reset(){
    SearchContext::$instance = null;
  }

  public function refresh(){
    if ($this->isNotEmpty()) {
      $this->execute();
    }
  }

  public function getDocumentById($id){
    $ctsearch_url = variable_get('ctsearch_url', '');
    $params = array(
      'mapping' => variable_get('ctsearch_mapping', ''),
      'doc_id' => $id,
    );
    $url = url($ctsearch_url, array('absolute' => true, 'query' => $params));
    $response = $this->getResponse($url->toString());
    if(isset($response['hits']['hits'][0])){
      return $response['hits']['hits'][0];
    }
    return null;
  }

  private function build(){
    $params = $_REQUEST;
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
    if(isset($params['ids'])){
      $this->ids = array_map('trim', explode(",", $params['ids']));
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
    if(isset($params['qs_filter'])){
      foreach($params['qs_filter'] as $advFilter){
        preg_match('/(?P<field>[^=]*)="(?P<value>[^"]*)"/', $advFilter, $matches);
        if(isset($matches['field']) && isset($matches['value'])){
          $this->advancedFilters[] = array(
            'field' => $matches['field'],
            'value' => $matches['value'],
          );
        }
      }
    }
  }

  /**
   * @return bool
   */
  private function isNotEmpty(){
    return (isset($this->query) && !empty($this->query) || !empty($this->filters) || !empty($this->ids)) && $this->query != '~';
  }

  public function execute($params = null){


    $ctsearch_url = variable_get('ctsearch_url', '');
    if($params == null) {
      $params = array(
        'mapping' => variable_get('ctsearch_mapping', ''),
        'facets' => variable_get('ctsearch_facets', ''),
      );
      if (variable_get('ctsearch_search_analyzer', null) != null && variable_get('ctsearch_search_analyzer', null) != '') {
        $params['analyzer'] = variable_get('ctsearch_search_analyzer', null);
      }
      if (variable_get('ctsearch_suggest_fields', null) != null && variable_get('ctsearch_suggest_fields', null) != '') {
        $params['suggest'] = variable_get('ctsearch_suggest_fields', null);
      }
      if (isset($this->query) && !empty($this->query)) {
        $params['query'] = $this->query;
      }
      else{
        $params['query'] = '*';
      }
      if (!empty($this->filters)) {
        $params['filter'] = $this->filters;
      }
      if (!empty($this->ids)) {
        $params['ids'] = implode(",", $this->ids);
      }
      if (!empty($this->facetOptions)) {
        foreach ($this->facetOptions as $facet_id => $options) {
          foreach ($options as $k => $v) {
            $params['facetOptions'][] = $facet_id . ',' . $k . ',' . $v;
          }
        }
      }
      if (!empty($this->advancedFilters)) {
        $params['qs_filter'] = [];
        foreach ($this->advancedFilters as $filter) {
          $params['qs_filter'][] = $filter['field'] . '="' . $filter['value'] . '"';
        }
      }
      $params['size'] = $this->size;
      $params['from'] = $this->from;
      $params['sort'] = $this->sort;
      if (variable_get('ctsearch_highlighted_fields', '') != '') {
        $params['highlights'] = variable_get('ctsearch_highlighted_fields', '');
      }
    }
    else{
      if(isset($params['size']))
        $this->size = $params['size'];
      if(isset($params['from']))
        $this->from = $params['from'];
      if(isset($params['sort']))
        $this->sort = $params['sort'];
      if(isset($params['ids']))
        $this->ids = $params['ids'];
    }


    $this->triggerBeforeHooks();

    $url = url($ctsearch_url, array('absolute' => true, 'query' => $params));
    $this->currentRequestUrl = $url;
    try {

      $response = $this->getResponse($url);
      if (isset($response['hits']['hits'])) {
        $this->results = $response['hits']['hits'];
      }
      if (isset($response['hits']['total'])) {
        $this->total = $response['hits']['total'];
      }
      if (isset($response['aggregations'])) {
        $this->facets = $response['aggregations'];
      }

      $this->triggerAfterHooks();

      if(isset($response['suggest_ctsearch']) && count($response['suggest_ctsearch']) > 0){
        $this->setDidYouMean($response['suggest_ctsearch'][0]['text']);
      }
      $this->status = SearchContext::CTSEARCH_STATUS_EXECUTED;
    }
    catch(\Exception $ex){
      drupal_set_message($ex->getMessage(), 'error');
    }
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
      throw new Exception("CtSearch response failed => code " . $code . ". Response is " . $r);
    }
  }

  public function buildFilterUrl($field, $value){
    $params = $_REQUEST;
    unset($params['facetOptions']);
    $params['filter'][] = $field . '="' . $value . '"';
    return url(current_path(), array('absolute' => true, 'query' => $params));
  }

  public function buildFilterRemovalUrl($field, $value){
    $params = $_REQUEST;
    unset($params['filter']);
    unset($params['facetOptions']);
    foreach($this->filters as $filter) {
      if($filter != $field . '="' . $value . '"') {
        $params['filter'][] = $filter;
      }
    }
    return url(current_path(), array('absolute' => true, 'query' => $params));
  }

  public function isFilterApplied($field, $value){
    return in_array($field . '="' . $value . '"', $this->filters);
  }

  public function getFacetRaiseSizeUrl($facet_id){
    $pace = 10;
    $new_size = isset($this->facetOptions[$facet_id]['size']) ? $this->facetOptions[$facet_id]['size'] + $pace : $pace * 2;
    $this->facetOptions[$facet_id]['size'] = $new_size;

    $params = $_REQUEST;
    if(isset($params['facetOptions'])) {
      unset($params['facetOptions']);
    }
    foreach($this->facetOptions as $facet_id => $options) {
      foreach($options as $k => $v){
        $params['facetOptions'][] = $facet_id . ',' . $k . ',' . $v;
      }
    }
    return url(current_path(), array('absolute' => true, 'query' => $params));
  }

  public function getPagedUrl($from = null, $sort = null){
    $params = $_REQUEST;
    if($from !== null) {
      $params['from'] = $from;
    }
    if($sort !== null) {
      $params['sort'] = $sort;
    }
    return url(current_path(), array('absolute' => true, 'query' => $params));
  }

  private function triggerBeforeHooks(){
    if (sizeof(module_implements('ctsearch_before_context_execute_alter')) > 0) {
      drupal_alter('ctsearch_before_context_execute', $this);
    }
  }

  private function triggerAfterHooks(){
    if (sizeof(module_implements('ctsearch_after_context_execute_alter')) > 0) {
      drupal_alter('ctsearch_after_context_execute', $this);
    }
  }

  /**
   * @return string
   */
  public function getQuery()
  {
    return isset($this->query) ? ($this->query != '*' ? $this->query : '') : null;
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
   * @return array
   */
  public function getAdvancedFilters()
  {
    return $this->advancedFilters;
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

  /**
   * @return string
   */
  public function getCurrentRequestUrl(){
    return $this->currentRequestUrl;
  }

  /**
   * @return string
   */
  public function getDidYouMean()
  {
    return $this->didYouMean;
  }

  /**
   * @param string $didYouMean
   */
  public function setDidYouMean($didYouMean)
  {
    $this->didYouMean = $didYouMean;
  }



}