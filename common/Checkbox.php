<?php
namespace pahanini\refiner\common;

use Yii;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Checkbox refiner
 * @author Pavel Tetyaev <pahanini@gmail.com>
 */
class Checkbox extends Base
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
        return $this->query($query);
    }

    public function all($query)
    {
        return $this->query($query);
    }

    public function modify($all, $active)
    {
        $result = [
            'all' => isset($all[0]['_count']) ? (int)$all[0]['_count'] : 0,
            'active' => isset($active[0]['_count']) ? (int)$active[0]['_count'] : 0,
        ];
        return $result;
    }

    public function query($query)
    {
        return $query->select(["COUNT(*) as _count"]);
    }

    public function refine($query, $params)
    {
        $query->andWhere(new Expression("$this->columnName IN (" . join(',', $params) . ")"));
        return $query;
    }
}