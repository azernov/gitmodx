#!/usr/bin/env php
<?php
/**
 * Script for automatic loading templates,
 * located at Data Base
 * to core/components/gitmodx/elements/templates/*.tpl
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

$savePath = MODX_CORE_PATH.'components/gitmodx/elements/templates/';
$staticFilePath = 'core/components/gitmodx/elements/templates/';
$templates = $modx->getCollection('modTemplate');
/**
 * @var modTemplate[] $templates
 */
foreach($templates as $template)
{
    /**
     * @var modCategory $cat
     */
    $cat = $template->getOne('Category');

    $arr = $template->toArray();
    $arr['category'] = $cat ? $cat->category : null;
    $arr['properties'] = '';
    if(!in_array($arr['category'],$excludedCategories) && !empty($arr['content']))
    {
        $content = $arr['content'];
        $name = trim($arr['templatename']).'.tpl';
        if(file_put_contents($savePath.$name,$content) === false){
            $modx->log(MODX_LOG_LEVEL_ERROR, "Error while saving template into file: \"{$name}\"");
            continue;
        }
        $template->set('static',1);
        $template->set('source',1);
        $template->set('static_file',$staticFilePath.$name);
        if($template->save()){
            $modx->log(MODX_LOG_LEVEL_INFO, "Template was imported: \"{$name}\"");
        }
    }
}

exit;