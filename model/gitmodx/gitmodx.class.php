<?php
include_once MODX_CORE_PATH . "model/modx/modx.class.php";

/**
 * Class gitModx
 * Extends standard MODx base class to allow work with elements without storing to database
 * @property gitModParser parser
 */
class gitModx extends modX
{

    /**
     * Override standard runSnippet
     * Firstly it search snippet in file. If not found - run parent method
     * @param string $snippetName
     * @param array $params
     * @return mixed|string
     */
    public function runSnippet($snippetName, array $params= array ()) {
        if ($this->getParser())
        {
            if($snippet = $this->parser->getElementFromFile('modSnippet',$snippetName))
            {
                /* @var modSnippet $snippet */
                $snippet->setCacheable(false);
                return $snippet->process($params);
            }
        }
        return parent::runSnippet($snippetName,$params);
    }

    /**
     * Override standard getChunk
     * Firstly it search chunk in file. If not found - run parent method
     * @param string $chunkName - через точку можно задать подкаталог (например tpl.header)
     * @param array $properties
     * @return string
     */
    public function getChunk($chunkName, array $properties= array ()) {
        if ($this->getParser())
        {
            if($chunk = $this->parser->getElementFromFile('modChunk',$chunkName))
            {
                /* @var modChunk $chunk */
                $chunk->setCacheable(false);
                return $chunk->process($properties);
            }
        }
        return parent::getChunk($chunkName,$properties);
    }

    /**
     * Override standard getObject method
     * It check if you want to get modChunk or modSnippet object
     * It look firstly in file and if not found - run parent method
     * @param string $className
     * @param null $criteria
     * @param bool $cacheFlag
     * @return null|object
     */
    public function getObject($className, $criteria= null, $cacheFlag= true) {
        switch($className)
        {
            case 'modChunk':
            case 'modSnippet':
                if(!isset($criteria['name'])){
                    foreach($criteria as $key => $value){
                        if(preg_match('#:?name:?#i',$key))
                        {
                            $criteria['name'] = $value;
                            break;
                        }
                    }
                    if(!isset($criteria['name'])) break;
                }

                if($this->getParser())
                {
                    if($element = $this->parser->getElementFromFile($className,$criteria['name']))
                    {
                        return $element;
                    }
                }
        }
        return parent::getObject($className,$criteria,$cacheFlag);
    }

    /**
     * Merge file based config to system config
     * Settings, defined in file based config override settings stored in database
     * To define file based settings - see components/gitmodx/config/config.inc.php
     * @access protected
     * @return boolean True if successful.
     */
    protected function _loadConfig() {
        parent::_loadConfig();
        $config = include MODX_CORE_PATH.'components/gitmodx/config/config.inc.php';
        $this->config = array_merge($this->config, $config);
        $this->_systemConfig= $this->config;
        return true;
    }

    /**
     * Loads file based context settings
     * WARNING!!! Context settings, stored in database override file based context settings
     * To define file based context settings - see components/gitmodx/config/[context_key]/config.inc.php
     * @param string $contextKey
     * @param bool $regenerate
     * @param null $options
     * @return bool
     */
    protected function _initContext($contextKey, $regenerate = false, $options = null) {
        $initialized = parent::_initContext($contextKey, $regenerate, $options);
        if($initialized){
            $fileName = MODX_CORE_PATH.'components/gitmodx/config/'.$this->context->key.'/config.inc.php';
            if(file_exists($fileName))
            {
                $config = include $fileName;
                $this->context->config = array_merge($config, $this->context->config);
                $this->config = array_merge($this->_systemConfig, $this->context->config);
            }
        }
        return $initialized;
    }
}