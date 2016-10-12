<?php

namespace Mirin;

use Nette;
use Nette\Application\Routers;

class RouterFactory
{
	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new Routers\RouteList;
		$router[] = new Routers\Route("/", "Index:default");
		return $router;
	}

}
