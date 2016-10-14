<?php
namespace Mirin\Components;
use Nette;

class Pagination extends Nette\Application\UI\Control
{

	/**
	 * @var Nette\Utils\Paginator
	 */
	private $paginator;

	/**
	 * @var string
	 */
	private $firstPageUrl;

	public function __construct(Nette\Utils\Paginator $paginator, $firstPageUrl)
	{
		$this->paginator = $paginator;
		$this->firstPageUrl = $firstPageUrl;
		parent::__construct();
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->firstPageUrl = $this->firstPageUrl;

		// sliding by 10 pages
		$pageOffset = 5;
		$pageCount = $this->paginator->getPageCount();
		$currentPage = $this->paginator->getPage();
		$firstPage = max(1, $currentPage - $pageOffset + 1);
		$lastPage = min($pageCount, $currentPage + $pageOffset);

		$template->pageCount = $pageCount;
		$template->firstPage = $firstPage;
		$template->setFile(__DIR__ . "/templates/pagination.latte");
		$template->render();
	}
}
