#!/usr/bin/env php
<?php
/**
 * Script for automatic loading snippets,
 * located at Data Base
 * to core/components/gitmodx/elements/snippets/*.php
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
    $arr['category'] = $cat ? $cat->category : null;
    $arr['properties'] = '';
    if(!in_array($arr['category'],$excludedCategories) && !in_array($arr['name'],$excludedSnippets))
    {
        $content = "<?php\n".$arr['snippet'];
        $name = trim($arr['name']).'.php';
        if(file_put_contents($savePath.$name,$content) === false){
            $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving snippet into file: \"{$name}\"");
            continue;
        }
        if($snippet->remove()){
            $modx->log(MODX_LOG_LEVEL_INFO, "Snippet was imported and deleted from database: \"{$name}\"");
        }
        else{
            $modx->log(MODX_LOG_LEVEL_ERROR, "Snippet was imported but NOT deleted from database: \"{$name}\"");
        }
    }
}

exit;