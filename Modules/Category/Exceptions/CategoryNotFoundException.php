<?php

namespace  Modules\Category\Exceptions;


class CategoryNotFoundException extends \Exception
{
	public function __construct()
	{
		parent::__construct(__("core::app.response.not-found", ["name" => "Category"]));
	}
}
