<?php

namespace  Modules\NavigationMenu\Exceptions;


class NavigationMenuNotFoundException extends \Exception
{
    // NavigationMenuNotFoundException
	public function __construct()
	{
		parent::__construct(__("core::app.response.not-found", ["name" => "Navigation Menu"]));
	}
}
