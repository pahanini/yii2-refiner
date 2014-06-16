Yii2 search refiner (facet search)
=======================================================

Helps to add additional 'where' conditions based on http request to active queries (refine search queries)
and also automates calculation of possible values of different filters used to build UI. Can be used in
RESTful APIs.

Terms
-----

"Base query" - ActiveQuery (e.g. MySqlQuery, SphinxQuery) to find all possible items before any refiner will
be applied. E.g. `SELECT * FROM products WHERE balance > 0`

"Refiner" is object which:
- calculates all possible (or independent) values for UI, e.g. list of possible product's categories, maximum and minimum
  price between products (all callback).
- analyzes http request and add additional where conditions to basic query (refine callback)
- calculates active values of UI. Active values depends on values of other refiners (active callback).

"Refiner Set" - object which contain one or more refiners and base query. Refiner set applies all refiners to query.


Installation
------------

Add

```"pahanini/yii2-refiner": "*"```

to the require section of your `composer.json` file.


Usage
-----

### Set

Refiner set has two main independent functions:

- getRefinedQuery - returns query modified by refiners
- getRefinerValues - returns all refiners values

### Refiner

Each refiner modifies base query according to http query params. To modify query refiner calls `refine($query, $params)`
callback function. This function must return modified query. Function example:

```
$this->refine = function($query, $params) {
    return $query->andWhere('has_discount > :val', [':val' => $params]);
}

```

Note:
- if http param does no exist refine function will not be called
- if $paramSeparator property is set and http param is not an array then http param wll converted
  to array using $paramSeparator property
- if $paramToArray property is set and http param is not an array then it will converted to array with one element
- if $paramType is set then http param type will be changed using php setType function


Some examples:

```
public function init()
{
	$this->refinerSet = new \pahanini\refiner\Set([
		'refiners' => [
			// standard range
			'price' => [
				'class' => '\pahanini\refiner\db\Range',
			],
			'category' => [
				'class' => '\pahanini\refiner\db\Count',
			],
			// Select color values from another model
			'onlyRedColor' => [
			    'refine' => function($query, $param) {
			        return $query->andWhere('color = "red"');
			    },
			    'all' => function($query) {
			        return $query->select('COUNT(*)')->andWhere('color = "red"')
			    }
			]
		]
	])
}


public function actionSearch()
{
	$query = Product::find()->andWhere('balance > 0');
	$refinerResult = $this->refinerSet->applyTo($query);
	$this->render('search', ['query' => $query, 'refiners' => $this->refinerSet->getRefinerValues()])
}

```
