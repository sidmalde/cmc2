<?php 
class Checkout
{

	public $stockItems = [
		'FR1' => [
			'name' => 'Fruit tea',
			'price' => 3.11,
			'discount' => [],
		],
		'SR1' => [
			'name' => 'Strawberries',
			'price' => 5,
			'discount' => [],
		],
		'CF1' => [
			'name' => 'Coffee',
			'price' => 11.23,
			'discount' => [],
		],
	];

	public $basket = [];
	public $total = 0.00;

	function __construct(array $pricingRules)
	{
		$this->setPriceRules($pricingRules);
	}

	function scan(string $productCode)
	{
		if (!empty($this->basket[$productCode])) {
			$this->basket[$productCode]++;
		} else {
			$this->basket[$productCode] = 1;
		}

		$tempTotal = 0.00;
		foreach ($this->basket as $item => $qty) {
			if (!empty($this->stockItems[$item]['discount'])) {
				if ($qty >= $this->stockItems[$item]['discount']['minItems']) {
					$multiplier = 1;
					if ($this->stockItems[$item]['discount']['discountType'] == 'value') {
						$this->stockItems[$item]['price'] -= $this->stockItems[$item]['discount']['discountValue'];
					} else if ($this->stockItems[$item]['discount']['discountType'] == 'percent') {
						$multiplier = $this->stockItems[$item]['discount']['discountValue']/100;
					}
 
					$tempTotal += ($this->stockItems[$item]['price'] * $qty * $multiplier);
						
				}
			} else {
				$tempTotal += ($this->stockItems[$item]['price'] * $qty);
			}
		}
		$this->total = $tempTotal;
	}



	function setPriceRules(array $pricingRules)
	{
		foreach($pricingRules as $ruleKey => $ruleConditions) {
			if ($ruleKey == 'BOGOF') {
				$this->stockItems[$ruleConditions['item']]['discount'] = [
					'minItems' => 2,
					'multi' => true,
					'discountValue' => 50,
					'discountType' => 'percent'
				];
			}

			if ($ruleKey == 'MULTIBUY') {
				$originalValue = $this->stockItems[$ruleConditions['item']]['price'];
				$newValue = $ruleConditions['newPrice'];
				$discount = $originalValue - $newValue;
				$this->stockItems[$ruleConditions['item']]['discount'] = [
					'minItems' => 3,
					'multi' => false,
					'discountValue' => $discount,
					'discountType' => 'value'
				];
			}
		}
	}
}

$pricingRules = [
	'BOGOF' => [
		'item' => 'FR1',
	],
	'MULTIBUY' => [
		'minItem' => 3,
		'item' => 'SR1',
		'newPrice' => 4.50
	]
];

$co = new Checkout($pricingRules);
$co->scan('FR1');
$co->scan('SR1');
$co->scan('FR1');
$co->scan('FR1');
$co->scan('CF1');
$price = $co->total;
var_dump($price);

$co = new Checkout($pricingRules);
$co->scan('FR1');
$co->scan('FR1');
$price = $co->total;
var_dump($price);

$co = new Checkout($pricingRules);
$co->scan('SR1');
$co->scan('SR1');
$co->scan('FR1');
$co->scan('SR1');
$price = $co->total;
var_dump($price);