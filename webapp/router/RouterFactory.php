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
		$router[] = new Routers\Route("/about", "About:default");
		$router[] = new Routers\Route("/blog/<slug [a-z0-9_-]+>", "BlogArticle:default");
		$router[] = new Routers\Route("/blog/category/<slug [a-z0-9-]+>", "BlogCategory:default");
		$router[] = new Routers\Route("/admin/login", "Admin:LogIn:default");
		$router[] = new Routers\Route("/admin/logout", "Admin:LogIn:logOut");
		$router[] = new Routers\Route("/admin/articles", "Admin:Articles:default");
		$router[] = new Routers\Route("/admin/articles/<id [0-9]+>", "Admin:EditArticle:default");
		$router[] = new Routers\Route("/admin/theme", "Admin:Theme:default");
		$router[] = new Routers\Route("/rss.xml", "Rss:default");
		return $router;
	}

}
