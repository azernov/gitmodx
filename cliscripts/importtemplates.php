#!/usr/bin/env php
<?
/**
 * Script for automatic loading templates,
 * located at Data Base
 * to core/components/gitmodx/elements/templates/*.tpl
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
    $arr['category'] = $cat->category;
    $arr['properties'] = '';
    if(!in_array($arr['category'],$excludedCategories) && !empty($arr['content']))
    {
        print_r($arr);
        $content = $arr['content'];
        $name = $arr['templatename'].'.tpl';
        file_put_contents($savePath.$name,$content);
        $template->set('static',1);
        $template->set('source',1);
        $template->set('static_file',$staticFilePath.$name);
        $template->save();
    }
}

exit;