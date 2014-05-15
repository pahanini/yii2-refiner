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
     * @var null|array Rules to modify final result
     */
    public $modify;

    /**
     * @var string Name of refiner
     */
    public $name;

    /**
     * @var array Describes how to merge of all and active values
     */
    public $on = ['id' => 'id'];

    /**
     * @var string|null
     */
    public $paramType;

    /**
     * @var Closure|callable valid callback to refine basic query
     */
    public $refine;

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
            call_user_func($this->apply, $query, $params);
        }
        return $this;
    }

    /**
     * Returns get params
     * @return array|mixed
     */
    public function getParams()
    {
        $result = Yii::$app->request->get($this->name);
        if ($this->paramType) {
            if (is_array($result)) {
                foreach ($result as &$val) {
                    $val = settype($val, $this->paramType);
                }
            } else {
                $result = settype($result, $this->paramType);
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
            $all = call_user_func($this->all, clone $query)->asArray()->all();
        }
        $active = [];
        if (is_callable($this->active)) {
            $query = clone $query;
            foreach ($this->set->getRefiners() as $refiner) {
                if ($refiner === $this) {
                    continue;
                }
                $refiner->applyTo($query);

            }
            $active = call_user_func($this->active, $query)->asArray()->all();
        }
        $result = $this->merge($all, $active, $this->on);
        if ($this->modify) {
            $result = $this->modify($result, $this->modify);
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
    protected function modify(&$array, $rules)
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