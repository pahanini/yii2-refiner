<?php
namespace pahanini\refiner\common;

use Yii;
use yii\base\Object;
use pahanini\refiner\Helper;

/**
 * Base refiner
 *
 * @author Pavel Tetyaev <pahanini@gmail.com>
 *
 * @property string columnName
 */
class Base extends Object
{
    /**
     * @var Closure|callable Valid callback to all possible (independent) values.
     */
    public $all;

    /**
     * @var Closure|callable valid callback to count active (depended) values
     */
    public $active;

    /**
     * @var null|array Rules to expand
     */
    public $expand;


    /**
     * @var string Name of refiner
     */
    public $name;

    /**
     * @var array Describes how to merge of all and active values
     */
    public $on = ['id' => 'id'];

    /**
     * @var bool if true param values converted to array
     */
    public $paramToArray = false;

    /**
     * @var string if  not null param value converted to array using this param as separator
     */
    public $paramSeparator;

    /**
     * @var string|null
     */
    public $paramType;

    /**
     * @var Closure|callable valid callback to refine basic query
     */
    public $refine;

    /**
     * @var null|array Rules to rename columns of final result
     */
    public $rename;

    /**
     * @var \pahanini\refiner\Set
     */
    public $set;

    /**
     * @var string Additional where clause to add to 'all' and 'active' queries
     */
    public $valueFilter;

    /**
     * @var string Name of column in database. If not set matches with name.
     */
    public $_columnName;

    /**
     * @param \yii\db\Query $query query to apply filters
     * @return array
     */
    public function applyTo($query)
    {
        if (is_callable($this->refine) && ($params = $this->getParams())) {
            call_user_func($this->refine, $query, $params);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return isset($this->_columnName) ? $this->_columnName : $this->name;
    }

    /**
     * Returns get param associated with refiner
     * @return mixed
     */
    public function getParams()
    {
        if (($result = Yii::$app->request->get($this->name)) === null) {
            return $result;
        };
        if ($this->paramSeparator && is_array($result)) {
            $result = explode($this->paramSeparator, $result);
        }
        if ($this->paramToArray && !is_array($result)) {
            $result = [$result];
        }
        if ($this->paramType) {
            if (is_array($result)) {
                foreach ($result as $key => $val) {
                    settype($result[$key], $this->paramType);
                }
            } else {
                settype($result, $this->paramType);
            }
        }
        return $result;
    }

    /**
     * @return array of values for UI
     */
    public function getValues()
    {
        $query = $this->set->getBaseQueryOrigin();
        if ($this->valueFilter) {
            $query->andWhere($this->valueFilter);
        }
        $all = [];
        if (is_callable($this->all)) {
            $tmp = clone $query;
            $all = $this->getValueCall('all', $tmp);
        }
        $active = [];
        if (is_callable($this->active)) {
            $tmp = clone $query;
            foreach ($this->set->getRefiners() as $refiner) {
                if ($refiner === $this) {
                    continue;
                }
                $refiner->applyTo($tmp);
            }
            $active = $this->getValueCall('active', $tmp);
        }
        return $this->modify($all, $active);
    }

    /**
     * @param $name
     * @param $query
     * @return mixed
     * @throws \Exception
     */
    private function getValueCall($name, $query)
    {
        $result = call_user_func($this->$name, $query);
        if ($result instanceof yii\db\ActiveQueryInterface) {
            $result = $result->asArray()->all();
        }
        if (!is_array($result)) {
            throw new \Exception("Unexpected return type of {$this->name}->$name callback'");
        }
        return $result;
    }

    /**
     * Modifies array with all and active values (merges, renames columns etc..)
     * @param $all
     * @param $active
     * @return mixed
     */
    protected function modify($all, $active)
    {
        $result = Helper::merge($all, $active, $this->on);
        if ($this->expand) {
            $result = Helper::expand($result, $this->expand);
        }
        if ($this->rename) {
            $result = Helper::rename($result, $this->rename);
        }
        return $result;
    }

    /**
     * @param $value
     */
    public function setColumnName($value)
    {
        $this->_columnName = $value;
    }
} 