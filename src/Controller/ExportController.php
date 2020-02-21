<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 24/02/2016
 * Time: 12:39
 */

namespace Drupal\ctsearch\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends ControllerBase
{

  public function export(){

    header('Content-type: text/xml;charset=utf-8');

    $types = \Drupal::request()->get('types');
    $page = \Drupal::request()->get('page') != null ? \Drupal::request()->get('page') : 1;

    $pageSize = 50;
    $offset = ($page - 1) * $pageSize;

    $criteria = [];
    foreach(explode('||', $types) as $et){
      if(count(explode('|', $et)) == 2){
        $entity_type = explode('|', $et)[0];
        $bundles = explode(',', explode('|', $et)[1]);
        $criteria[$entity_type] = $bundles;
      }
    }
    print '<?xml version="1.0" encoding="UTF-8"?><entities>';
    foreach($criteria as $entity_type => $bundles) {
      $query = \Drupal::entityQuery($entity_type);
      $queryBundles = true;
      foreach($bundles as $bundle){
        if($bundle == '*')
          $queryBundles = false;
      }
      if($queryBundles) {
        $query->condition('type', $bundles, 'IN');
      }
      $query
        ->sort(\Drupal::entityTypeManager()->getDefinition($entity_type)->getKey('id'), 'ASC')
        ->range($offset, $pageSize);
      $result = $query->execute();
      foreach($result as $entity_id){
        $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);

        print ExportController::serializeToXml($entity);
      }
    }
    print '</entities>';

    return new Response('', 200, array('Content-Type' => 'text/xml;charset=utf-8'));

  }

  /**
   * @param Entity $entity
   * @return string
   */
  public static function getExportId(EntityInterface $entity){
    return preg_replace("/[^A-Za-z0-9 ]/", '_', $_SERVER['HTTP_HOST']) . '_' . $entity->getEntityTypeId() . '_' . $entity->get(\Drupal::entityTypeManager()->getDefinition($entity->getEntityTypeId())->getKey('id'))->value;
  }

  /**
   * @param integer $nid
   * @param Entity $entity
   * @return string
   */
  public static function serializeToXml($entity){
    if(is_object($entity)){
      if(!in_array("getType", get_class_methods($entity))){
        return '';
      }
      $xml = '<entity id="' . $entity->get(\Drupal::entityTypeManager()->getDefinition($entity->getEntityTypeId())->getKey('id'))->value . '">';

      $xml .= '<export-id>' . ExportController::getExportId($entity) . '</export-id>';
      $xml .= '<entity-type>' . $entity->getEntityTypeId() . '</entity-type>';
      $xml .= '<url><![CDATA[' . $entity->url('canonical', array('absolute' => true)) . ']]></url>';
      $xml .= '<bundle><![CDATA[' . $entity->getType() . ']]></bundle>';

      if(method_exists($entity,'getFields')){
        foreach(array_keys($entity->getFields()) as $fieldName){
          //var_dump($fieldName . ' --> ' . $node->get($fieldName)->getFieldDefinition()->getType() . ' --> ' . $node->get($fieldName)->value);
          //var_dump($fieldName . ' --> ' . $node->get($fieldName)->getFieldDefinition()->getType());
          $values = $entity->get($fieldName)->getValue();
          if(!empty($values)){
            $xml .= '<' . $fieldName . '>';

            switch($entity->get($fieldName)->getFieldDefinition()->getType()){
              case 'file':
              case 'image':
                foreach($values as $delta => $value){
                  $xml .= '<value type="file" fid="' . $value['target_id'] . '" delta="' . $delta . '"><![CDATA[' . File::load($value['target_id'])->url('canonical', array('absolute' => true)) . ']]></value>';
                }
                break;
              case 'entity_reference':
                foreach($values as $delta => $value){
                  $targetType = $entity->get($fieldName)->getFieldDefinition()->getSetting('target_type');
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
                      // Handle drupal interval dates
                      if (isset($value['end_value'])) {
                          $xml .= '<value type="' . $entity->get($fieldName)->getFieldDefinition()->getType() . '" delta="' . $delta . '"><![CDATA[' . $value['value'] . ']]></value>';
                          $xml .= '<value type="' . $entity->get($fieldName)->getFieldDefinition()->getType() . '" delta="' . ($delta+1) . '"><![CDATA[' . $value['end_value'] . ']]></value>';
                      } else {
                        $xml .= '<value type="' . $entity->get($fieldName)->getFieldDefinition()->getType() . '" delta="' . $delta . '"><![CDATA[' . $value['value'] . ']]></value>';
                      }
                  }
                }
                break;
            }
            $xml .= '</' . $fieldName . '>';
          }
        }
        $xml .= '</entity>';
        return $xml;
      }else{
        return '';
      }
    }else{
      return '';
    }
  }

  /**
   * @param Node $node
   */
  public static function handleEntityUpdate($entity){
    if(\Drupal::config('ctsearch.settings')->get('ctsearch_autoindex')){
      ExportController::pushContent(array($entity));
    }
  }

  /**
   * @param Node $node
   */
  public static function handleEntityDelete($entity){
    if(\Drupal::config('ctsearch.settings')->get('ctsearch_autoindex')){
      ExportController::deleteContent($entity);
    }
  }

  private static function pushContent($nodes){
    $url = \Drupal::config('ctsearch.settings')->get('ctsearch_index_url');
    $datasource_id = \Drupal::config('ctsearch.settings')->get('ctsearch_datasource_id');
    $target_mapping = \Drupal::config('ctsearch.settings')->get('ctsearch_target_mapping');
    if(!empty($url) && !empty($datasource_id) && !empty($target_mapping)){

      $xml = '<?xml version="1.0"?><entities>';
      foreach($nodes as $node){
        $xml .= ExportController::serializeToXml($node);
      }
      $xml .= '</entities>';

      $data = 'id=' . urlencode($datasource_id) . '&xml=' . urlencode($xml);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      $r = curl_exec($ch);

      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if($http_code != 200){
        drupal_set_message(t('An error has occured while sending content to CtSearch server'), 'error');
      }
    }
    else{
      drupal_set_message(t('Ct search configuration is incorrect'), 'error');
    }
  }

  private static function deleteContent($entity){
    $url = \Drupal::config('ctsearch.settings')->get('ctsearch_index_url');
    $datasource_id = \Drupal::config('ctsearch.settings')->get('ctsearch_datasource_id');
    $target_mapping = \Drupal::config('ctsearch.settings')->get('ctsearch_target_mapping');
    if(!empty($url) && !empty($datasource_id) && !empty($target_mapping)){

      $data = 'id=' . urlencode($datasource_id) . '&item_id=' . urlencode(ExportController::getExportId($entity)) . '&target_mapping=' . urlencode($target_mapping);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      $r = curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if($http_code != 200){
        drupal_set_message(t('An error has occured while deleting content on CtSearch server : '), 'error');
      }
    }
    else{
      drupal_set_message(t('Ct search configuration is incorrect'), 'error');
    }
  }
}
