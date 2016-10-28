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
		$router[] = new Routers\Route("/rss.xml", "Rss:default");

		$router[] = new Routers\Route("/admin/login", "Admin:LogIn:default");
		$router[] = new Routers\Route("/admin/logout", "Admin:LogIn:logOut");

		$router[] = new Routers\Route("/admin/theme", "Admin:Theme:default");

		$router[] = new Routers\Route("/admin/articles", "Admin:Articles:default");
		$router[] = new Routers\Route("/admin/articles/add", "Admin:NewArticle:default");
		$router[] = new Routers\Route("/admin/articles/<id [0-9]+>", "Admin:EditArticle:default");
		$router[] = new Routers\Route("/admin/articles/preview/<slug [a-z0-9_-]+>", "Admin:PreviewArticle:default");
		// redirects into /admin/articles, because it's same destination, and first defined route is the
		// main route
		$router[] = new Routers\Route("/admin", "Admin:Articles:default");

		$router[] = new Routers\Route("/admin/comments", "Admin:Comments:default");
		$router[] = new Routers\Route("/admin/comments/<id [0-9]+>", "Admin:EditComment:default");
		$router[] = new Routers\Route("/admin/comments/delete/<id [0-9]+>", "Admin:DeleteComment:default");

		return $router;
	}

}
