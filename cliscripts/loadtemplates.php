#!/usr/bin/env php
<?php
/**
 * Script for automatic loading templates,
 * located at core/components/gitmodx/elements/templates/*.tpl
 * to Data Base as static templates
 */
define("MODX_API_MODE",true);
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

$modx->setLogTarget('ECHO');
$modx->setLogLevel(MODX_LOG_LEVEL_INFO);

$firstTemplate = $modx->getObject('modTemplate',1);

$files = glob(dirname(dirname(__FILE__)).'/elements/templates/*.tpl');
$indexWasSaved = false;
foreach($files as $file)
{
    $content = file_get_contents($file);
    $pieces = explode('/',$file);
    $name = end($pieces);
    $name = trim(preg_replace('#\.tpl$#ui','',$name));
    $fileRelative = str_replace(MODX_BASE_PATH,'',$file);

    if($template = $modx->getObject('modTemplate',array(
        'static_file' => $fileRelative
    )))
    {
        $modx->log(MODX_LOG_LEVEL_INFO,'Template '.$name.' is already in the database');
        continue;
    }

    /**
     * @var modTemplate $template
     */
    if($name == 'index' || $name == 'home')
    {
        $template = &$firstTemplate;
        $indexWasSaved = true;
    }
    else
    {
        $template = $modx->newObject('modTemplate');
    }
    $template->set('source',1);
    $template->set('templatename',$name);
    $template->set('static',true);
    $template->set('static_file',$fileRelative);
    if($template->save())
    {
        $modx->log(MODX_LOG_LEVEL_INFO,'Saved new template: '.$name);
    }
    else
    {
        $modx->log(MODX_LOG_LEVEL_ERROR,'Can not save template '.$name);
    }
}