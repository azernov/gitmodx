#!/usr/bin/env php
<?php

/**
 * Automaticaly replace include path and class name to gitmodx in index.php files
 */

include dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/'.'config.core.php';
include MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';

$files = array(
    MODX_BASE_PATH.'index.php',
    MODX_CONNECTORS_PATH.'index.php',
    MODX_MANAGER_PATH.'index.php'
);

foreach($files as $file){
    $content = file_get_contents($file);
    $content = str_replace('model/modx/modx.class.php','components/gitmodx/model/gitmodx/gitmodx.class.php',$content);
    $content = str_replace('new modX(','new gitModx(',$content);
    file_put_contents($file,$content);
}