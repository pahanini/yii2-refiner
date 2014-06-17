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
    public $paramToArray = false;

    public $paramType = 'bool';

    public function init()
    {
        parent::init();
        if ($this->refine === null) {
            $this->refine = [$this, 'refine'];
        }
        if ($this->all === null) {
            $this->all = [$this, 'all'];
        }
        if ($this->active === null) {
            $this->active = [$this, 'active'];
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
        $query->andWhere($this->valueFilter);
        return $query;
    }
}