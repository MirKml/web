<?php
namespace Mirin\Components;
use Nette;

/**
 * Pagination for list of items
 */
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

	protected function getTemplateFile()
	{
		return __DIR__ . "/templates/pagination.latte";
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->firstPageUrl = $this->firstPageUrl;

		// sliding by 10 pages
		$pageOffset = 5;
		$pageCount = $this->paginator->getPageCount();
		$currentPage = $this->paginator->getPage();
		$firstRangePage = max(1, $currentPage - $pageOffset + 1);
		$lastRangePage = min($pageCount, $firstRangePage + $pageOffset * 2 - 1);

		$template->currentPage = $currentPage;
		$template->firstRangePage = $firstRangePage;
		$template->lastRangePage = $lastRangePage;
		$template->pageCount = $pageCount;
		$template->setFile($this->getTemplateFile());
		$template->render();
	}
}
