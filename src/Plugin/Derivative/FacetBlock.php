<?php
/**
 * Created by PhpStorm.
 * User: louis
 * Date: 17/02/2016
 * Time: 20:50
 */

namespace Drupal\ctsearch\Plugin\Derivative;


use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FacetBlock extends DeriverBase implements ContainerDeriverInterface
{

  public static function create(ContainerInterface $container, $base_plugin_id)
  {
    return new static();
  }

  public function getDerivativeDefinitions($base_plugin_definition)
  {
    $facets = array_map('trim', explode(',', \Drupal::config('ctsearch.settings')->get('facets')));
    foreach($facets as $facet){
      $this->derivatives[$facet] = $base_plugin_definition;
      $this->derivatives[$facet]['admin_label'] = t('Facet "@facet_name"', array('@facet_name' => $facet));
    }
    return $this->derivatives;
  }
}