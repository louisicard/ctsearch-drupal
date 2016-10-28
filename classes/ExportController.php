<?php

class ExportController
{

  public function export()
  {

    header('Content-type: text/xml;charset=utf-8');

    $types = isset($_REQUEST['types']) ? $_REQUEST['types'] : '';
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

    $pageSize = 50;
    $offset = ($page - 1) * $pageSize;

    $criteria = [];
    foreach (explode('||', $types) as $et) {
      if (count(explode('|', $et)) == 2) {
        $entity_type = explode('|', $et)[0];
        $bundles = explode(',', explode('|', $et)[1]);
        $criteria[$entity_type] = $bundles;
      }
    }
    print '<?xml version="1.0" encoding="UTF-8"?><entities>';
    foreach ($criteria as $entity_type => $bundles) {
      $query = new EntityFieldQuery();
      $query->entityCondition('entity_type', $entity_type);
      $queryBundles = true;
      foreach ($bundles as $bundle) {
        if ($bundle == '*')
          $queryBundles = false;
      }
      if ($queryBundles) {
        $query->entityCondition('type', $bundles, 'IN');
      }
      $query
        ->propertyOrderBy('nid', 'ASC')
        ->range($offset, $pageSize);
      $result = $query->execute();
      if (!empty($result['node'])) {
        foreach (array_keys($result['node']) as $nid) {
          $node = node_load($nid);
          if ($node) {
            print ExportController::serializeToXml($node);
          }
        }
      }
    }
    print '</entities>';

  }

  public static function getExportId($type, $nid)
  {
    return preg_replace("/[^A-Za-z0-9 ]/", '_', $_SERVER['HTTP_HOST']) . '_' . $type . '_' . $nid;
  }

  public static function serializeToXml($node)
  {
    global $base_url;
    if ($node) {
      $xml = '<entity id="' . $node->nid . '">';

      $xml .= '<export-id>' . ExportController::getExportId($node->type, $node->nid) . '</export-id>';
      $xml .= '<entity-type>node</entity-type>';
      $xml .= '<url><![CDATA[' . $base_url . '/node/' . $node->nid . ']]></url>';
      $xml .= '<bundle><![CDATA[' . $node->type . ']]></bundle>';

      foreach (array_keys(get_object_vars($node)) as $fieldName) {
        if (!empty($node->{$fieldName}) && !in_array($fieldName, ['data', 'rdf_mapping'])) {

          $xml .= '<' . $fieldName . '>';

          $value = $node->{$fieldName};

          if(is_array($value)){
            $count = 0;
            foreach($value as $lang => $values){
              if(is_array($values)) {
                foreach ($values as $v) {
                  $xml .= '<value delta="' . $count . '" lang="' . $lang . '"';
                  if (isset($v['tid'])) {
                    $term = taxonomy_term_load($v['tid']);
                    $xml .= ' tid="' . $v['tid'] . '" type="taxonomy_term">' . ($term ? '<![CDATA[' . $term->name . ']]>' : '');
                  } elseif (isset($v['uri']) && isset($v['fid'])) {
                    $xml .= ' fid="' . $v['fid'] . '" type="file"><![CDATA[' . file_create_url($v['uri']) . ']]>';
                  } elseif (isset($v['target_id'])) {
                    $xml .= ' type="node-reference"><![CDATA[http://' . $base_url . '/node/' . $v['target_id'] . ']]>';
                  } elseif (isset($v['value'])) {
                    $xml .= ' type="string"><![CDATA[' . $v['value'] . ']]>';
                  } else {
                    $xml .= '>';
                  }
                  $xml .= '</value>';
                  $count++;
                }
              }
            }
          }elseif(!is_object($value)){
            $xml .= '<value type="'. (is_numeric($value) ? 'number' : 'string') .'" delta="0">' . (is_numeric($value) ? $value : '<![CDATA[' . $value . ']]>') . '</value>';
          }
          $xml .= '</' . $fieldName . '>';

        }
      }
      $xml .= '</entity>';
      return $xml;
    } else {
      return '';
    }
  }

  public static function handleEntityUpdate($node)
  {
    if (variable_get('ctsearch_autoindex', false)) {
      return ExportController::pushContent(array($node));
    }
    return false;
  }

  public static function handleEntityDelete($node)
  {
    if (variable_get('ctsearch_autoindex', false)) {
      return ExportController::deleteContent($node);
    }
    return false;
  }

  private static function pushContent($nodes)
  {
    $url = variable_get('ctsearch_index_url', '');
    $datasource_id = variable_get('ctsearch_datasource_id', '');
    $target_mapping = variable_get('ctsearch_target_mapping', '');
    if (!empty($url) && !empty($datasource_id) && !empty($target_mapping)) {

      $xml = '<?xml version="1.0"?><entities>';
      foreach ($nodes as $node) {
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
      if ($http_code != 200) {
        drupal_set_message(t('An error has occured while sending content to CtSearch server'), 'error');
        return false;
      }
      else{
        return true;
      }
    } else {
      drupal_set_message(t('Ct search configuration is incorrect'), 'error');
    }
    return false;
  }

  private static function deleteContent($node)
  {
    $url = variable_get('ctsearch_index_url', '');
    $datasource_id = variable_get('ctsearch_datasource_id', '');
    $target_mapping = variable_get('ctsearch_target_mapping', '');
    if (!empty($url) && !empty($datasource_id) && !empty($target_mapping)) {

      $data = 'id=' . urlencode($datasource_id) . '&item_id=' . urlencode(ExportController::getExportId($node->type, $node->nid)) . '&target_mapping=' . urlencode($target_mapping);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      $r = curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($http_code != 200) {
        drupal_set_message(t('An error has occured while deleting content on CtSearch server : '), 'error');
        return false;
      }
      else{
        return true;
      }
    } else {
      drupal_set_message(t('Ct search configuration is incorrect'), 'error');
    }
    return false;
  }
}