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

	protected  function modify($all, $active)
	{
		return [
			'all' => ['max' => $all[0]['_max'], 'min' => $all[0]['_min']],
			'active' => ['max' => $active[0]['_max'], 'min' => $active[0]['_min']],
		];
	}

	public function refine($query, $params)
	{
		if (isset($params['min'])) {
			$query->andWhere("$this->name >= :param", [':param' => $params['min']]);
		}
		if (isset($params['max'])) {
			$query->andWhere("$this->name >= :param", [':param' => $params['max']]);
		}
		return $query;
	}
}