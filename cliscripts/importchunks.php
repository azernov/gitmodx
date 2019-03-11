#!/usr/bin/env php
<?php
/**
 * Script for automatic loading chunks,
 * located at Data Base
 * to core/components/gitmodx/elements/chunks/*.tpl
 */
define('MODX_API_MODE', true);
//define('XPDO_CLI_MODE',false);
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

$modx->setLogTarget('ECHO');
$modx->setLogLevel(MODX_LOG_LEVEL_INFO);

//Set categories to ignore import
$excludedCategories = array(
    'AjaxForm',
    'Articles',
    'miniShop2',
    'Archivist',
    'BreadCrumb',
    'FormIt',
    'pdoTools',
    'Quip',
    'tagLister',
    'ms2Gallery'
);

$savePath = MODX_CORE_PATH.'components/gitmodx/elements/chunks/';
$chunks = $modx->getCollection('modChunk');
/**
 * @var modChunk[] $chunks
 */
foreach($chunks as $chunk)
{
    /**
     * @var modCategory $cat
     */
    $cat = $chunk->getOne('Category');

    $arr = $chunk->toArray();
    //$arr['content'] = $arr['snippet'] = '';
    $arr['category'] = $cat ? $cat->category : null;
    $arr['properties'] = '';
    if(!in_array($arr['category'],$excludedCategories))
    {
        $content = $arr['snippet'];
        $name = trim($arr['name']).'.tpl';
        if(file_put_contents($savePath.$name,$content) === false){
            $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving chunk into file: \"{$name}\"");
            continue;
        }
        if($chunk->remove()){
            $modx->log(MODX_LOG_LEVEL_INFO, "Chunk was imported and deleted from database: \"{$name}\"");
        }
        else{
            $modx->log(MODX_LOG_LEVEL_ERROR, "Chunk was imported but NOT deleted from database: \"{$name}\"");
        }
    }
}

exit;