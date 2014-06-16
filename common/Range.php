<?php
namespace pahanini\refiner\common;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Common range refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Range extends Base
{
    public $paramType = 'float';

    public function init()
    {
        parent::init();
        if (!$this->all) {
            $this->all = [$this, 'all'];
        }
        if (!$this->active) {
            $this->active = [$this, 'active'];
        }
        if (!$this->refine) {
            $this->refine = [$this, 'refine'];
        }
    }

    public function all($query)
    {
        return $query->select([new Expression("1 as _id, min($this->columnName) as _min, max($this->columnName) as _max")]);
    }

    public function active($query)
    {
        return $query->select([new Expression("1 as _id, min($this->columnName) as _min, max($this->columnName) as _max")]);
    }

    protected function modify($all, $active)
    {
        $result = [];
        if ($all) {
            $result['all'] = [
                'max' => isset($all[0]['_max']) ? $all[0]['_max'] : 0,
                'min' => isset($all[0]['_min']) ? $all[0]['_min'] : 0
            ];
            settype($result['all']['max'], $this->paramType);
            settype($result['all']['min'], $this->paramType);
        }
        if ($active) {
            $result['active'] = [
                'max' => isset($active[0]['_max']) ? $active[0]['_max'] : 0,
                'min' => isset($active[0]['_min']) ? $active[0]['_min'] : 0
            ];
            settype($result['active']['max'], $this->paramType);
            settype($result['active']['min'], $this->paramType);
        }
        return $result;
    }

    public function refine($query, $params)
    {
        if (isset($params['min'])) {
            $query->andWhere("$this->columnName >= :{$this->columnName}Min", [":{$this->columnName}Min" => $params['min']]);
        }
        if (isset($params['max'])) {
            $query->andWhere("$this->columnName <= :{$this->columnName}Max", [":{$this->columnName}Max" => $params['max']]);
        }
        return $query;
    }
}