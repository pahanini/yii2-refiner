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
     * @var string Name of default refiner class
     */
    public $defaultRefinerClass = '\pahanini\refiner\Base';

    /**
     * @var null|QueryInterface
     */
    private $_query;

    /**
     * @var array
     */
    private $_refinerInstances;

    /**
     * @var array refiners config
     */
    private $_refiners;

    /**
     * @var null|QueryInterface
     */
    private $_save;


    /**
     * Refines base query
     * @throws InvalidConfigException
     */
    public function apply()
    {
        if (!$this->_query) {
            throw new \Exception("Query must be set before call apply method");
        }
        foreach ($this->getRefiners() as $refiner) {
            $refiner->applyTo($this->_query);
        }
        return $this;
    }

    public function applyTo($query)
    {
        $this->setQuery($query);
        return $this->apply();
    }

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

    public function getValues()
    {
        $result = [];
        foreach ($this->getRefiners() as $key => $refiner) {
            $result[$key] = $refiner->getValue(clone $this->_save);

        }
        return $result;
    }

    public function setRefiners($config)
    {
        $this->_refiners = $config;
    }

    /**
     * @param $query
     */
    public function setQuery(QueryInterface $query)
    {
        $this->_query = $query;
        $this->_save = clone $query;
        $this->_save->with = null;
    }
}