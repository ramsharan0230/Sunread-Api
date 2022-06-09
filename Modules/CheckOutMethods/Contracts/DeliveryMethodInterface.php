<?php

namespace Modules\CheckOutMethods\Contracts;

interface DeliveryMethodInterface
{
	public function get(): mixed;

	// public function calculatedValue(?callable $callback = null): mixed;
}
