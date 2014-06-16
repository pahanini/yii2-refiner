<?php
namespace pahanini\refiner\common;

use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Count refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Count extends Base
{
    public $on = ['_id' => '_id'];

    public $paramToArray = true;

    public $paramType = 'int';

    public $rename = ['_id' => 'id'];

    public function init()
    {
        parent::init();
        if (!$this->refine) {
            $this->refine = [$this, 'refine'];
        }
        if (!$this->all) {
            $this->all = [$this, 'all'];
        }
        if (!$this->active) {
            $this->active = [$this, 'active'];
        }
        if (!$this->on) {
            $this->on = ['_id' => '_id'];
        }
    }

    public function active($query)
    {
        return $this->query($query, 'active');
    }

    public function all($query)
    {
        return $this->query($query, 'all');
    }

    public function query($query, $name)
    {
        return $query
            ->select([new Expression($this->columnName . ' as _id'), "COUNT(*) as $name"])
            ->andWhere(new Expression("$this->columnName IS NOT NULL"))
            ->groupBy([new Expression($this->columnName)]);
    }

    public function refine($query, $params)
    {
        $query->andWhere(new Expression("$this->columnName IN (" . join(',', $params) . ")"));
        return $query;
    }
}