<?php
namespace Mirin\AdminModule\Components;
use Nette;

class Menu extends Nette\Application\UI\Control
{
	const ITEM_ARTICLES = "articles";
	const ITEM_COMMENTS = "comments";

	/**
	 * current item,
	 * one of the constants ITEM_*
	 * @var string
	 */
	private $currentItemId;

	public function __construct($currentItemId)
	{
		if (!in_array($currentItemId, [self::ITEM_ARTICLES, self::ITEM_COMMENTS])) {
			throw new \InvalidArgumentException("item with id '$currentItemId' doesn't exist");
		}
		$this->currentItemId = $currentItemId;
		parent::__construct();
	}

	public function render()
	{
		$item = new \stdClass();
		$item->name = "Články";
		$item->url = $this->getPresenter()->lazyLink(":Admin:Articles:");
		$item->active = $this->currentItemId == self::ITEM_ARTICLES;
		$item->glyphicon = "glyphicon-list";
		$items[] = $item;

		$item = new \stdClass();
		$item->name = "Komentáře";
		$item->url = $this->getPresenter()->lazyLink(":Admin:Comments:");
		$item->active = $this->currentItemId == self::ITEM_COMMENTS;
		$item->glyphicon = "glyphicon-briefcase";
		$items[] = $item;

		$template = $this->getTemplate();
		$template->items = $items;
		$template->setFile(__DIR__ . "/templates/menu.latte");
		$template->render();
	}
}