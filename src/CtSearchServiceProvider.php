<?php
/**
 * Created by PhpStorm.
 * User: louissicard
 * Date: 17/05/2016
 * Time: 21:38
 */

namespace Drupal\ctsearch;


use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class CtSearchServiceProvider extends ServiceProviderBase
{


    public function register(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds("ctsearch.listener");
        $listeners = array();
        foreach(array_keys($services) as $id){
            $listeners[] = $id;
        }
        $container->setParameter('ctsearch.listeners', $listeners);
    }


}