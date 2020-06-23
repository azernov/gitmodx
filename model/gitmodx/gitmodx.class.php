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
     * Fenom-compatible getCount
     * @param string $className
     * @param null $criteria
     * @return int
     */
    public function getCount($className, $criteria = null){
        switch($className)
        {
            case 'modChunk':
            case 'modSnippet':
                if(is_array($criteria)){
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
                            return 1;
                        }
                    }
                }
                break;
        }
        return parent::getCount($className,$criteria);
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

                if($this->getParser() && $this->parser instanceof gitModParser)
                {
                    if($element = $this->parser->getElementFromFile($className,$criteria['name']))
                    {
                        return $element;
                    }
                }
                break;
            case 'modPlugin':
                if(isset($criteria['id'])){
                    if($this->getParser() && $this->parser instanceof gitModParser)
                    {
                        if($element = $this->parser->getElementFromFileById($className,$criteria['id']))
                        {
                            return $element;
                        }
                    }
                }
                break;
        }
        return parent::getObject($className,$criteria,$cacheFlag);
    }

    /**
     * Gets a map of events and registered plugins for the specified context.
     *
     * Service #s:
     * 1 - Parser Service Events
     * 2 - Manager Access Events
     * 3 - Web Access Service Events
     * 4 - Cache Service Events
     * 5 - Template Service Events
     * 6 - User Defined Events
     *
     * @param string $contextKey Context identifier.
     * @return array A map of events and registered plugins for each.
     */
    public function getEventMap($contextKey) {
        $eventElementMap = parent::getEventMap($contextKey);

        if($contextKey){
            //Extending standard event-plugin map by file-based plugins
            $pluginsConfigFile = dirname(dirname(dirname(__FILE__))).'/elements/plugins/plugins.inc.php';
            if(file_exists($pluginsConfigFile)){
                $gitPluginsMap = include $pluginsConfigFile;
                $eventElementMap = is_array($eventElementMap) ? $eventElementMap : array();
                foreach($gitPluginsMap as $eventName => $pluginNames){
                    if(is_string($pluginNames)) $pluginNames = array($pluginNames);

                    if(!isset($eventElementMap[$eventName])) $eventElementMap[$eventName] = array();
                    foreach($pluginNames as $pluginName){
                        $eventElementMap[$eventName][crc32($pluginName)] = crc32($pluginName);
                    }
                }
            }
        }

        return $eventElementMap;
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
        $dir = opendir(MODX_CORE_PATH.'components/gitmodx/config/');
        while($file = readdir($dir))
        {
            if(preg_match('/\.inc\.php/i',$file))
            {
                $config = include MODX_CORE_PATH.'components/gitmodx/config/'.$file;
                $this->config = array_merge($this->config, $config);
            }
        }
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
            if(is_dir(MODX_CORE_PATH.'components/gitmodx/config/'.$this->context->key))
            {
                $dir = opendir(MODX_CORE_PATH.'components/gitmodx/config/'.$this->context->key);
                while($file = readdir($dir))
                {
                    if(preg_match('/\.inc\.php/i',$file))
                    {
                        $config = include MODX_CORE_PATH.'components/gitmodx/config/'.$this->context->key.'/'.$file;
                        $this->context->config = array_merge($config, $this->context->config);
                        $this->config = array_merge($this->_systemConfig, $this->context->config);
                    }
                }
            }
        }
        return $initialized;
    }

    /**
     * Returns settings with specified context
     * @param $contextKey
     * @return array|mixed
     */
    public function getConfigWithContext($contextKey){
        $config = $this->_systemConfig;

        /** @var modContext $context */
        $context = $this->getObject('modContext',array(
            'key' => $contextKey
        ));
        $context->prepare();
        if(is_dir(MODX_CORE_PATH.'components/gitmodx/config/'.$contextKey))
        {
            $dir = opendir(MODX_CORE_PATH.'components/gitmodx/config/'.$contextKey);
            while($file = readdir($dir))
            {
                if(preg_match('/\.inc\.php/i',$file))
                {
                    $config = include MODX_CORE_PATH.'components/gitmodx/config/'.$contextKey.'/'.$file;
                    $context->config = array_merge($config, $context->config);
                    $config = array_merge($this->_systemConfig, $context->config);
                }
            }
        }

        return $config;
    }

    /**
     * Search context key by setting key and value
     * @param $key
     * @param $value
     * @return bool|false|string
     */
    public function findContextBySetting($key, $value){
        //Open base config dir
        if(is_dir(MODX_CORE_PATH.'components/gitmodx/config')) {
            $dir = opendir(MODX_CORE_PATH . 'components/gitmodx/config');
            while ($contextKey = readdir($dir)) {
                //Searching context directories
                if(is_dir(MODX_CORE_PATH.'components/gitmodx/config/'.$contextKey)) {
                    $dir2 = opendir(MODX_CORE_PATH . 'components/gitmodx/config/' . $contextKey);
                    while ($file = readdir($dir2)) {
                        if (preg_match('/\.inc\.php/i', $file)) {
                            $config = include MODX_CORE_PATH . 'components/gitmodx/config/' . $contextKey . '/' . $file;
                            if(array_key_exists($key, $config) && $config[$key] == $value){
                                return $contextKey;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}