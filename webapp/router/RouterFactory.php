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
		$router[] = new Routers\Route("/<slug [a-z0-9_-]+>", "BlogArticle:default");
		$router[] = new Routers\Route("/category/<slug [a-z0-9-]+>", "BlogCategory:default");
		$router[] = new Routers\Route("/rss.xml", "Rss:default");
		return $router;
	}

}
