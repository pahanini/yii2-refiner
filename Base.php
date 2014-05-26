<?php
namespace pahanini\refiner;

use Yii;
use yii\base\Object;

/**
 * Base refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Base extends Object
{
    /**
     * @var Closure|callable Valid callback to all possible (independed) values.
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
     * @var string
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
     * Renames or deleted values of array
     */
    protected function expand($array, $rules)
    {
        foreach ($array as $key => $value) {
            foreach ($rules as $rule) {
                if (array_key_exists($rule, $value) && is_array($value[$rule])) {
                    $array[$key] = array_merge($array[$key], $value[$rule]);
                    unset($array[$key][$rule]);
                }
            }
        }
        return $array;
    }

    /**
     * Returns get params
     * @return array|mixed
     */
    public function getParams()
    {
        $result = Yii::$app->request->get($this->name);
        if ($result !== null && $this->paramType) {
            if ($this->paramSeparator) {
                $result = explode($this->paramSeparator, $result);
            }
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
     * @param $query
     * @return array of values for UI
     */
    public function getValue($query)
    {
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
        $result = $this->merge($all, $active, $this->on);
        if ($this->expand) {
            $result = $this->expand($result, $this->expand);
        }
        if ($this->rename) {
            $result = $this->rename($result, $this->rename);
        }
        return $result;
    }

    /**
     * Merges two arrays using specified values
     * @param $array1
     * @param $array2
     * @param $on
     * @return mixed
     */
    protected function merge($array1, $array2, $on)
    {
        list($on1, $on2) = each($on);
        $array2 = \yii\helpers\ArrayHelper::index($array2, $on2);
        foreach ($array1 as $k1 => $v1) {
            if (!array_key_exists($on1, $v1)) {
                continue;
            }
            $k2 = $v1[$on1];
            if (isset($array2[$k2])) {
                $v2 = $array2[$k2];
                unset($v2[$on2]);
                $array1[$k1] = array_merge($v1, $v2);
            }
        }
        return $array1;
    }

    /**
     * Renames or deleted values of array
     */
    protected function rename($array, $rules)
    {
        foreach ($array as $key => $value) {
            foreach ($rules as $rKey => $rValue) {
                if (array_key_exists($rKey, $value)) {
                    if ($rValue !== false) {
                        $array[$key][$rValue] = $value[$rKey];
                    }
                    unset($array[$key][$rKey]);
                }
            }
        }
        return $array;
    }
} 