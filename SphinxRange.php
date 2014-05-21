<?php
namespace pahanini\refiner;

use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * SphinxRange refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class SphinxRange extends Base
{
    public function init()
    {
        parent::init();
        $this->all = [$this, 'all'];
        $this->active = [$this, 'active'];
        $this->refine = [$this, 'refine'];
        $this->paramType = 'float';
    }

    public function all($query)
    {
        return $query->select([new Expression("1 as _id, min($this->name) as _min, max($this->name) as _max")]);
    }

    public function active($query)
    {
        return $query->select([new Expression("1 as _id, min($this->name) as _min, max($this->name) as _max")]);
    }

    protected function modify($all, $active)
    {
        return [
            'all' => [
                'max' => isset($all[0]['_max']) ? $all[0]['_max'] : 0,
                'min' => isset($all[0]['_min']) ? $all[0]['_min'] : 0
            ],
            'active' => [
                'max' => isset($active[0]['_max']) ? $active[0]['_max'] : 0,
                'min' => isset($active[0]['_min']) ? $active[0]['_min'] : 0
            ],
        ];
    }

    public function refine($query, $params)
    {
        // params converted to in due https://github.com/yiisoft/yii2/issues/3513
        if (isset($params['min'])) {
            $query->andWhere("$this->name >= :{$this->name}Min", [":{$this->name}Min" => (int)$params['min']]);
        }
        if (isset($params['max'])) {
            $query->andWhere("$this->name <= :{$this->name}Max", [":{$this->name}Max" => (int)$params['max']]);
        }
        return $query;
    }
}