Yii2 search refiner (facet search)
=======================================================

Helps to add additional 'where' conditions based on http request to db queries (refine search queries)
and also automates calculation of possible values of different filters used to build UI. Can be in
RESTful APIs.

Terms
-----

"Base query" - ActiveQuery (e.g. MysqlQuery, SphinxQuery) to find all possible items before any refiner will
be applied. E.g. `SELECT * FROM products WHERE balance > 0`

"Refiner" is object which:
- calculates all possible values for UI, e.g. list of possible product's categories, maximum and minimum
  price between products (independet values).
- analyzes http request and add additional where conditions to basic query
- calculates active values of UI. Active values depends on values of other refiners.

"Refiner Set" - object which contain one or more refiners an basic query.


Installation
------------

Add

```"pahanini/yii2-refiner": "*"```

to the require section of your `composer.json` file.


Usage
-----


```
public function init()
{
	$this->refinerSet = new \pahanini\refiner\Set([
		'refiners' => [
			// standard range
			'price' => [
				'class' => '\pahanini\refiner\SphinxRange',
			],
			'has_discount' => [
				'class' => '\pahanini\refiner\SphinxJsonBool',
			],
			// Select color values from another model
			'color' => [
				'all' => function($query) {
					return \common\models\Color::find();
				},
			]
		]
	])
}


public function actionSearch()
{
	$query = Product::find()->andWhere('balance > 0');
	$refinerResult = $this->refinerSet->applyTo($query);
	$this->render('search', ['query' => $query, 'refiners' => $this->refinerSet->getValues()])
}

```
