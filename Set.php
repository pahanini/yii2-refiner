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
     * @return yii\db\QueryInterface
     */
    public function getBaseQuery()
    {
        return $this->_baseQuery;
    }

    /**
     * @return null|QueryInterface
     */
    public function getBaseQueryOrigin()
    {
        return clone $this->_baseQueryOrigin;
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
        if (!$this->_refinerInstances) {
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