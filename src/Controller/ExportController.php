<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 24/02/2016
 * Time: 12:39
 */

namespace Drupal\ctsearch\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Entity;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends ControllerBase
{

  public function export(){

    $type = \Drupal::request()->get('type');
    $page = \Drupal::request()->get('page') != null ? \Drupal::request()->get('page') : 1;

    $pageSize = 50;
    $offset = ($page - 1) * $pageSize;

    $query = \Drupal::entityQuery('node')
      ->sort('nid', 'ASC')
      ->range($offset, $pageSize);
    if($type != null){
      $query->condition('type', $type);
    }

    $result = $query->execute();

    $xml = '<?xml version="1.0" encoding="UTF-8"?><nodes>';

    foreach ($result as $nid => $res) {
      $node = Node::load($nid);
      $xml .= ExportController::serializeToXml($node);
    }

    $xml .= '</nodes>';

    return new Response($xml, 200, array('Content-type' => 'text/xml;charset=utf-8'));
  }

  /**
   * @param Node $node
   * @return string
   */
  public static function getExportId($node){
    return preg_replace("/[^A-Za-z0-9 ]/", '_', $_SERVER['HTTP_HOST']) . '_' . $node->get('nid')->value;
  }

  /**
   * @param integer $nid
   * @param Node $node
   * @return string
   */
  public static function serializeToXml($node){
    $xml = '<node nid="' . $node->get('nid')->value . '">';
    $xml .= '<export-id>' . ExportController::getExportId($node) . '</export-id>';
    $xml .= '<url><![CDATA[' . $node->url('canonical', array('absolute' => true)) . ']]></url>';
    $xml .= '<type><![CDATA[' . $node->getType() . ']]></type>';

    foreach(array_keys($node->getFields()) as $fieldName){
      //var_dump($fieldName . ' --> ' . $node->get($fieldName)->getFieldDefinition()->getType() . ' --> ' . $node->get($fieldName)->value);
      //var_dump($node->get($fieldName)->getValue());
      $values = $node->get($fieldName)->getValue();
      if(!empty($values)){
        $xml .= '<' . $fieldName . '>';
        switch($node->get($fieldName)->getFieldDefinition()->getType()){
          case 'file':
          case 'image':
            foreach($values as $delta => $value){
              $xml .= '<value type="file" fid="' . $value['target_id'] . '" delta="' . $delta . '"><![CDATA[' . File::load($value['target_id'])->url('canonical', array('absolute' => true)) . ']]></value>';
            }
            break;
          case 'entity_reference':
            foreach($values as $delta => $value){
              $targetType = $node->get($fieldName)->getFieldDefinition()->getSetting('target_type');
              if($targetType == 'taxonomy_term'){
                $term = Term::load($value['target_id']);
                $xml .= '<value type="taxonomy_term" tid="' . $value['target_id'] . '" delta="' . $delta . '"><![CDATA[' . $term->get('name')->value . ']]></value>';
              }
              else {
                $xml .= '<value type="entity_reference" target_type="' . $targetType . '" target_id="' . $value['target_id'] . '" delta="' . $delta . '"><![CDATA[]]></value>';
              }
            }
            break;
          default:
            if(isset($values[0]['value'])) {
              foreach ($values as $delta => $value) {
                $xml .= '<value type="' . $node->get($fieldName)->getFieldDefinition()->getType() . '" delta="' . $delta . '"><![CDATA[' . $value['value'] . ']]></value>';
              }
            }
            break;
        }
        $xml .= '</' . $fieldName . '>';
      }
    }

    $xml .= '</node>';
    return $xml;
  }

  /**
   * @param Node $node
   */
  public static function handleNodeUpdate($node){
    if(\Drupal::config('ctsearch.settings')->get('ctsearch_autoindex')){
      ExportController::pushContent(array($node));
    }
  }

  /**
   * @param Node $node
   */
  public static function handleNodeDelete($node){
    if(\Drupal::config('ctsearch.settings')->get('ctsearch_autoindex')){

    }
  }

  private static function pushContent($nodes){
    $url = \Drupal::config('ctsearch.settings')->get('ctsearch_index_url');
    $datasource_id = \Drupal::config('ctsearch.settings')->get('ctsearch_datasource_id');
    $target_mapping = \Drupal::config('ctsearch.settings')->get('ctsearch_target_mapping');
    if(!empty($url) && !empty($datasource_id) && !empty($target_mapping)){

      $xml = '<?xml version="1.0"?><nodes>';
      foreach($nodes as $node){
        $xml .= ExportController::serializeToXml($node);
      }
      $xml .= '</nodes>';

      $data = 'id=' . urlencode($datasource_id) . '&xml=' . urlencode($xml);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if($http_code != 200){
        drupal_set_message(t('An error has occured while sending content to CtSearch server'), 'error');
      }
    }
    else{
      drupal_set_message(t('Ct search configuration is incorrect'), 'error');
    }
  }
}