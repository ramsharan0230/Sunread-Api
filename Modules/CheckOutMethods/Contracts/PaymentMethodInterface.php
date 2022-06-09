<?php

namespace Modules\CheckOutMethods\Contracts;

interface PaymentMethodInterface
{
	public function get(): mixed;

	// public function calculatedValue(?callable $callback = null): mixed;
}
