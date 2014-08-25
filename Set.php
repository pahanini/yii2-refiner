<?php
namespace pahanini\refiner;

use Yii;
use yii\base\Object;
use yii\db\QueryInterface;

/**
 * Refiners set. Represents information relevant to refiners of query.
 *
 * @author Pavel Tetyaev <pahanini@gmail.com>
 * @property [] $refiners Array of refiners. Read-only.
 */
class Set extends Object
{
    /**
     * @var Cache|string the cache object or the ID of the cache application component
     * that is used for query caching.
     */
    public $cache = 'cache';

    /**
     * @var integer the default number of seconds that query results can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * Defaults to 3600, meaning 3600 seconds, or one hour. Use 0 to indicate that the cached data will never expire.
     * The value of this property will be used when [[cache()]] is called without a cache duration.
     * @see cache()
     */
    public $cacheDuration = 3600;

    /**
     * @var bool whether refiner should return active values by default
     */
    public $defaultReturnActiveValues = true;

    /**
     * @var bool whether refiner should return all values by default
     */
    public $defaultReturnAllValues = true;

    /**
     * @var string Name of default refiner class
     */
    public $defaultRefinerClass = '\pahanini\refiner\common\Base';

    /**
     * @var boolean whether to enable results caching.
     */
    public $enableCache = true;

    /**
     * @var int
     */
    private $_cacheDuration;

    /**
     * @var \yii\caching\Dependency
     */
    private $_cacheDependency;

    /**
     * @var null|QueryInterface
     */
    private $_baseQuery;

    /**
     * @var null|QueryInterface
     */
    private $_baseQueryOrigin;

    /**
     * @var array
     */
    private $_refinerInstances;

    /**
     * @var array refiners config
     */
    private $_refiners;

    /**
     * @param $baseQuery
     * @return yii\db\QueryInterface
     */
    public function applyTo($baseQuery)
    {
        $this->setBaseQuery($baseQuery);
        return $query = $this->getRefinedQuery();
    }

    /**
     * Enables query cache for this refiners
     * @param integer $duration the number of seconds that query result of this command can remain valid in the cache.
     * If this is not set, the value of [[Connection::queryCacheDuration]] will be used instead.
     * Use 0 to indicate that the cached data will never expire.
     * @param \yii\caching\Dependency $dependency the cache dependency associated with the cached query result.
     * @return static the command object itself
     */
    public function cache($duration = null, $dependency = null)
    {
        $this->_cacheDuration = $duration === null ? $this->defaultCacheDuration : $duration;
        $this->_cacheDependency = $dependency;
        return $this;
    }

    /**
     * @return yii\db\QueryInterface
     */
    public function getBaseQuery()
    {
        return $this->_baseQuery;
    }

    /**
     * @return \yii\db\QueryInterface
     */
    public function getBaseQueryOrigin()
    {
        return clone $this->_baseQueryOrigin;
    }

    /**
     * Returns the current refiners cache information.
     * This method is used internally by refiners
     * @param \yii\db\ActiveQuery $query
     * @return array the current query cache information, or null if query cache is not enabled.
     * @internal
     */
    public function getCacheInfo($query = null)
    {
        if ($this->cache) {
            if (is_string($this->cache) && Yii::$app) {
                $cache = Yii::$app->get($this->cache, false);
            } else {
                $cache = $this->cache;
            }
            if ($cache instanceof \yii\caching\Cache) {
                $query = $query ? $query : $this->getBaseQueryOrigin();
                $command = $query->createCommand();
                $cacheKey = array_merge(array_keys($this->_refiners), [
                            get_class($this),
                            $command->getRawSql(),
                            $command->db->dsn,
                            $command->db->username,
                        ]);
                return [$cache, $this->_cacheDuration, $this->_cacheDependency, $cacheKey];
            }
        }
        return null;
    }

    /**
     * Refines base query and return result
     * @throws \Exception
     * @return yii\db\QueryInterface
     */
    public function getRefinedQuery()
    {
        if (!$query = $this->getBaseQuery()) {
            throw new \Exception("Query must be set before call apply method");
        }
        foreach ($this->getRefiners() as $refiner) {
            $refiner->applyTo($query);
        }
        return $query;
    }

    /**
     * @param $name
     * @return pahanini\refiner\Base
     * @throws \Exception
     */
    public function getRefiner($name)
    {
        $refiners = $this->getRefiners();
        if (!isset($refiners[$name])) {
            throw new \Exception("Invalid refiner name $name");
        }
        return $refiners[$name];
    }


    /**
     * @return array Refiners instances
     */
    public function getRefiners()
    {
        if ($this->_refinerInstances === null) {
            $this->_refinerInstances = [];
            if (is_array($this->_refiners)) {
                foreach ($this->_refiners as $key => $config) {
                    if (!($config instanceof Base)) {
                        if (!isset($config['class'])) {
                            $config['class'] = $this->defaultRefinerClass;
                        }
                        if (!isset($config['name'])) {
                            $config['name'] = $key;
                        }
                        $config['set'] = $this;
                        $this->_refinerInstances[$key] = Yii::createObject($config);
                    }
                }
            }
        }
        return $this->_refinerInstances;
    }

    /**
     * @return array Refiners values
     */
    public function getRefinerValues()
    {
        $result = [];
        foreach ($this->getRefiners() as $key => $refiner) {
            $result[$key] = $refiner->getValues();
        }
        return $result;
    }

    /**
     * Sets refiners
     * @param $config
     */
    public function setRefiners($config)
    {
        $this->_refiners = $config;
    }

    /**
     * @param $query
     */
    public function setBaseQuery(QueryInterface $query)
    {
        $this->_baseQuery = $query;
        $this->_baseQueryOrigin = clone $query;
        $this->_baseQueryOrigin->with = null;
    }
}