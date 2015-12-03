<?php
namespace Wsbox\Util;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\PhpFileCache;
use Wsbox\Util\YamlParser;

/**
 * We use this class to read config files, for example, the site config.yml files.
 */
class AppConfigFileReader
{
    private $appRoot;
    private $cache;
    private $debug;

    /**
     * Constructor: set up cache provider and debug mode
     *
     * @param string $appRoot the root folder of the app
     * @param bool   $debug    debug flag
     *
     * @return void
     */
    public function __construct($appRoot, $debug = false)
    {
        $this->appRoot = $appRoot;
        $this->debug = (bool) $debug;

        $this->cache = $debug?  new ArrayCache : new PhpFileCache(PHP_FILE_CACHE_DIR);
        $this->cache->setNamespace('wsbox_api_config_');
    }

    /**
     * Inject a different cache object for caching. In case you want to overwrite caching.
     *
     * @param Cache $cache doctrine cache provider
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Return the current cache object
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get the cache key based on config file name
     *
     * @param string $configFileName config yml file name
     *
     * @return string
     */
    public function getCacheKey($configFileName)
    {

        return  basename($configFileName) . '-' . md5($configFileName);
    }

    /**
     * Get the config yml file from cache. If it's not there, create it.
     *
     * @param string $configFileName     config yml file name
     * @param string $testConfigFileName unit tests configuration file name. It will overwrite some of the main config
     *
     * @return array config array
     */
    public function get($configFileName, $testConfigFileName = null)
    {

        $cacheKey = $this->getCacheKey($configFileName);
        if (false === ($config = $this->cache->fetch($cacheKey))) {
            $config = $this->parse($configFileName, $testConfigFileName);
            $config['appRoot'] = $this->appRoot;
            $config['fileCacheDir'] = PHP_FILE_CACHE_DIR . '/';
            $config['debug']   = $this->debug;

            $this->cache->save($cacheKey, $config);
        }

        return $config;
    }

    /**
     * Read the config yml files and parse it.
     *
     * @param string $configFileName     config yml file name
     * @param string $testConfigFileName unit tests configuration file name. It will overwrite some of the main config
     *
     * @return array config array
     */
    private function parse($configFileName, $testConfigFileName)
    {
        $config = YamlParser::parse($configFileName);

        // if there is a test config file passed in, use it to overwrite main config file
        if (!empty($testConfigFileName)) {
            $testConfig = YamlParser::parse($testConfigFileName);
            if (!empty($testConfig)) {
                $config = array_replace_recursive($config, $testConfig);
            }
        }

        if (isset($config['configs'])) {
            foreach ($config['configs'] as $cfg) {
                $config = array_merge($config, YamlParser::parse($this->appRoot . $cfg));
            }
        }

        return $config;
    }
}
