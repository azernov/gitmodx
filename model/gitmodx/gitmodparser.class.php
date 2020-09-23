<?php

$defaultParentFile = MODX_CORE_PATH . "model/modx/modparser.class.php";
$pdoParserFile = MODX_CORE_PATH . "components/pdotools/model/pdotools/pdoparser.class.php";

if(file_exists($pdoParserFile)){
    include_once $pdoParserFile;
    class middleParser extends pdoParser {};
}
else{
    include_once $defaultParentFile;
    class middleParser extends modParser {};
}

/**
 * Class gitModParser
 * Extends standard class of modx parser to make possible store chunks and snippets in files without storing in database
 */
class gitModParser extends middleParser {
    private function globRecursive($pattern, $flags = 0)
    {
        $cacheKey = md5($pattern.$flags);
        $cacheManager = $this->modx->getCacheManager();
        $cachedGlobs = $cacheManager->get('gitmodparser_globs');
        if(!$cachedGlobs){
            $cachedGlobs = [];
        }


        if(isset($cachedGlobs[$cacheKey])){
            return $cachedGlobs[$cacheKey];
        }
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, $this->globRecursive($dir.'/'.basename($pattern), $flags));
        }
        $cachedGlobs[$cacheKey] = $files;
        $cacheManager->set('gitmodparser_globs', $cachedGlobs);
        return $files;
    }

    /**
     * Search file recursively
     * @param $path
     * @param $filename
     * @return bool|string
     */
    private function searchFile($path,$filename)
    {
        if($files = $this->globRecursive($path.$filename)){
            return $files[0];
        }

        return false;
    }

    /**
     * Search file by crc32 from the filename (without ext)
     * @param $path
     * @param $crc32
     * @param $ext
     * @return bool|string
     */
    private function searchFileByCrc32($path, $crc32, $ext){
        $files = $this->globRecursive($path.'*'.$ext);
        if($files){
            foreach($files as $file){
                $pathinfo = pathinfo($file);

                if($pathinfo['filename'] && crc32($pathinfo['filename']) == $crc32){
                    return $file;
                }
            }
        }

        return false;
    }

    /**
     * Loads file based element (modChunk or modSnippet)
     * @param $class
     * @param $name
     * @return bool|modSnippet|modChunk|modPlugin|null
     */
    public function getElementFromFile($class, $name) {

        $searchPathRel = $this->modx->getOption('site_elements_path','',str_replace(MODX_BASE_PATH, '', MODX_CORE_PATH).'components/gitmodx/elements/');
        $searchPath = MODX_BASE_PATH.$searchPathRel;

        //search for chunk in package directory
        if($class == 'modChunk') {
            $searchFolder = $searchPath.'chunks/';
            $searchFile = $name.'.tpl';

            $foundFilePath = $this->searchFile($searchFolder,$searchFile);


            //create chunk if we found one
            if($foundFilePath) {
                /* @var modChunk $chunk */
                $chunk = $this->modx->newObject('modChunk');
                $chunk->set('name', $name);
                $chunk->set('source', 0); //media source id
                //$chunk->set('static', 1); //create chunk as static file
                $chunk->set('static_file', $foundFilePath);
                $chunk->set('snippet', file_get_contents($foundFilePath));
                return $chunk;
            }
        }

        //search for snippet in package directory
        elseif($class == 'modSnippet') {
            $searchFolder = $searchPath.'snippets/';
            $searchFile = $name.'.php';
            $foundFilePath = $this->searchFile($searchFolder,$searchFile);

            //create snippet if we found one
            if($foundFilePath) {
                /* @var modSnippet $snippet */
                $snippet = $this->modx->newObject('modSnippet');
                $snippet->set('name', $name);
                $snippet->set('source', 0); //media source id
                //$snippet->set('static', 0); //create chunk as static file
                $snippet->set('static_file', $foundFilePath);
                $snippet->set('snippet', file_get_contents($foundFilePath));
                //We need to set unique id for correct caching. crc32 - is one of the ways
                $snippet->set('id',crc32($name));
                return $snippet;
            }
        }

        //search for plugin in plugin directory
        elseif($class == 'modPlugin') {
            $searchFolder = $searchPath.'plugins/';
            $searchFile = $name.'.php';
            $foundFilePath = $this->searchFile($searchFolder, $searchFile);

            if($foundFilePath){
                /* @var modPlugin $plugin */
                $plugin = $this->modx->newObject('modPlugin');
                $plugin->set('name',$name);
                $plugin->set('source', 0); //media source id
                //$plugin->set('static', 1); //create chunk as static file
                $plugin->set('static_file', $foundFilePath);
                $plugin->set('plugincode', file_get_contents($foundFilePath));
                //We need to set unique id for correct caching. crc32 - is one of the ways
                $plugin->set('id',crc32($name));
                return $plugin;
            }
        }

        return false;
    }

    /**
     * Loads file based element (modChunk or modSnippet or modPlugin) by id
     * @param $class
     * @param int $id
     * @return bool|modSnippet|modChunk|modPlugin|null
     */
    public function getElementFromFileById($class, $id) {

        $searchPathRel = $this->modx->getOption('site_elements_path','',str_replace(MODX_BASE_PATH, '', MODX_CORE_PATH).'components/gitmodx/elements/');
        $searchPath = MODX_BASE_PATH.$searchPathRel;

        //search for chunk in package directory
        if($class == 'modChunk') {
            $searchFolder = $searchPath.'chunks/';

            $foundFilePath = $this->searchFileByCrc32($searchFolder,$id, '.tpl');
            $foundFilePieces = explode('/',$foundFilePath);
            $fileName = end($foundFilePieces);
            $fileNamePcs = explode('.',$fileName);
            array_pop($fileNamePcs);
            $name = implode('.',$fileNamePcs);


            //create chunk if we found one
            if($foundFilePath) {
                /* @var modChunk $chunk */
                $chunk = $this->modx->newObject('modChunk');
                $chunk->set('name', $name);
                $chunk->set('source', 0); //media source id
                //$chunk->set('static', 1); //create chunk as static file
                $chunk->set('static_file', $foundFilePath);
                $chunk->set('snippet', file_get_contents($foundFilePath));
                return $chunk;
            }
        }

        //search for snippet in package directory
        elseif($class == 'modSnippet') {
            $searchFolder = $searchPath.'snippets/';

            $foundFilePath = $this->searchFileByCrc32($searchFolder,$id, '.php');
            $foundFilePieces = explode('/',$foundFilePath);
            $fileName = end($foundFilePieces);
            $fileNamePcs = explode('.',$fileName);
            array_pop($fileNamePcs);
            $name = implode('.',$fileNamePcs);

            //create snippet if we found one
            if($foundFilePath) {
                /* @var modSnippet $snippet */
                $snippet = $this->modx->newObject('modSnippet');
                $snippet->set('name', $name);
                $snippet->set('source', 0); //media source id
                //$snippet->set('static', 1); //create chunk as static file
                $snippet->set('static_file', $foundFilePath);
                $snippet->set('snippet', file_get_contents($foundFilePath));
                //We need to set unique id for correct caching. crc32 - is one of the ways
                $snippet->set('id',$id);
                return $snippet;
            }
        }

        //search for plugin in plugin directory
        elseif($class == 'modPlugin') {
            $searchFolder = $searchPath.'plugins/';

            $foundFilePath = $this->searchFileByCrc32($searchFolder,$id, '.php');
            $foundFilePieces = explode('/',$foundFilePath);
            $fileName = end($foundFilePieces);
            $fileNamePcs = explode('.',$fileName);
            array_pop($fileNamePcs);
            $name = implode('.',$fileNamePcs);

            if($foundFilePath){
                /* @var modPlugin $plugin */
                $plugin = $this->modx->newObject('modPlugin');
                $plugin->set('id',$id);
                $plugin->set('name',$name);
                $plugin->set('source', 0); //media source id
                //$plugin->set('static', 1); //create chunk as static file
                $plugin->set('static_file', $foundFilePath);
                $plugin->set('plugincode', file_get_contents($foundFilePath));
                return $plugin;
            }
        }

        return false;
    }


    /**
     * Get a modElement instance taking advantage of the modX::$sourceCache.
     *
     * @param string $class The modElement derivative class to load.
     * @param string $name An element name or raw tagName to identify the modElement instance.
     * @return modElement|null An instance of the specified modElement derivative class.
     */
    public function getElement($class, $name) {
        $realname = $this->realname($name);
        if (array_key_exists($class, $this->modx->sourceCache) && array_key_exists($realname, $this->modx->sourceCache[$class])) {
            /** @var modElement $element */
            $element = $this->modx->newObject($class);
            $element->fromArray($this->modx->sourceCache[$class][$realname]['fields'], '', true, true);
            $element->setPolicies($this->modx->sourceCache[$class][$realname]['policies']);

            if (!empty($this->modx->sourceCache[$class][$realname]['source'])) {
                if (!empty($this->modx->sourceCache[$class][$realname]['source']['class_key'])) {
                    $sourceClassKey = $this->modx->sourceCache[$class][$realname]['source']['class_key'];
                    $this->modx->loadClass('sources.modMediaSource');
                    /* @var modMediaSource $source */
                    $source = $this->modx->newObject($sourceClassKey);
                    $source->fromArray($this->modx->sourceCache[$class][$realname]['source'],'',true,true);
                    $element->addOne($source,'Source');
                }
            }
        } else {
            /** @var modElement $element */
            $element = $this->modx->getObjectGraph($class,array('Source' => array()),array('name' => $realname), true);
            if ($element && array_key_exists($class, $this->modx->sourceCache)) {
                $this->modx->sourceCache[$class][$realname] = array(
                    'fields' => $element->toArray(),
                    'policies' => $element->getPolicies(),
                    'source' => $element->Source ? $element->Source->toArray() : array(),
                );
            }
            elseif(!$element) {
                $element = $this->getElementFromFile($class,$realname);
                if ($element && array_key_exists($class, $this->modx->sourceCache)) {
                    $this->modx->sourceCache[$class][$realname] = array(
                        'fields' => $element->toArray(),
                        'policies' => $element->getPolicies(),
                        'source' => [
                            'id' => 0,
                            'name' => '',
                            'description' => '',
                            'class_key' => 'sources.modFileMediaSource',
                            'properties' => [],
                            'is_stream' => true,
                        ]
                    );
                }
                elseif(!$element)
                {
                    $this->modx->log(MODX_LOG_LEVEL_ERROR,'Element ('.$class.') not found: '.$name);
                    $evtOutput = $this->modx->invokeEvent('OnElementNotFound', array('class' => $class, 'name' => $realname));
                    $element = false;
                    if ($evtOutput != false) {
                        foreach ((array) $evtOutput as $elm) {
                            if (!empty($elm) && is_string($elm)) {
                                $element = $this->modx->newObject($class, array(
                                    'name' => $realname,
                                    'snippet' => $elm
                                ));
                            }
                            elseif ($elm instanceof modElement ) {
                                $element = $elm;
                            }

                            if ($element) {
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($element instanceof modElement) {
            $element->set('name', $name);
        }
        return $element;
    }
}
