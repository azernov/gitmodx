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

//Set categories to ignore import
$excludedCategories = array(
    'AjaxForm',
    'Articles',
    'miniShop2'
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
    $arr['category'] = $cat->category;
    $arr['properties'] = '';
    if(!in_array($arr['category'],$excludedCategories))
    {
        $content = $arr['snippet'];
        $name = $arr['name'].'.tpl';
        file_put_contents($savePath.$name,$content);
        $chunk->remove();
    }
}

exit;