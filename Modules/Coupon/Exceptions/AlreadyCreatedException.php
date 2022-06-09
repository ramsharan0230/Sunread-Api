<?php

namespace  Modules\Coupon\Exceptions;

class AlreadyCreatedException extends \Exception
{
	public function __construct()
	{
		parent::__construct("Requested data already created.");
	}
}
