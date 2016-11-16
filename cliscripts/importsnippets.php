#!/usr/bin/env php
<?
/**
 * Script for automatic loading snippets,
 * located at Data Base
 * to core/components/gitmodx/elements/snippets/*.php
 */
define('MODX_API_MODE', true);
//define('XPDO_CLI_MODE',false);
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

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
    'tagLister'
);

//Set snippets to ignore import
$excludedSnippets = array(
    'getResources',
    'Wayfinder',
    'GoogleSiteMap'
);
$savePath = MODX_CORE_PATH.'components/gitmodx/elements/snippets/';
$snippets = $modx->getCollection('modSnippet');
/**
 * @var modSnippet[] $snippets
 */
foreach($snippets as $snippet)
{
    /**
     * @var modCategory $cat
     */
    $cat = $snippet->getOne('Category');

    $arr = $snippet->toArray();
    //$arr['content'] = $arr['snippet'] = '';
    $arr['category'] = $cat->category;
    $arr['properties'] = '';
    if(!in_array($arr['category'],$excludedCategories) && !in_array($arr['name'],$excludedSnippets))
    {
        $content = "<?php\n".$arr['snippet'];
        $name = $arr['name'].'.php';
        file_put_contents($savePath.$name,$content);
        $snippet->remove();
    }
}

exit;