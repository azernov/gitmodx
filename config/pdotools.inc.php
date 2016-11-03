<?php
$pdoToolsConfig = array(
    //Set to false if you don't want cache fenom-compiled templates
    'pdotools_fenom_cache' => true,
    //Set to false if you want to use standard MODx syntax
    'pdotools_fenom_default' => true,
    'pdotools_fenom_parser' => true,
    //Set to true if you want to use {$modx->...} in your fenom templates
    'pdotools_fenom_modx' => false,
    'pdotools_fenom_options' => '',
    'pdotools_elements_path' => '{core_path}components/gitmodx/elements/chunks/',
);
return $pdoToolsConfig;
