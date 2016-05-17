<?php
/**
 * Created by PhpStorm.
 * User: louissicard
 * Date: 17/05/2016
 * Time: 22:02
 */

namespace Drupal\ctsearch;


interface CtSearchEventListener
{

    function beforeExecute(&$params);
    function afterExecute(&$results);

}