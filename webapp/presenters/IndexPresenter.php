<?php
namespace Mirin\Presenters;

use Nette;
use Mirin\Model;

class IndexPresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @inject
	 * @var Model\BlogEntryRepository
	 */
	public $blogEntryRepository;

	public function renderDefault()
	{
		$paginatorFactory = new PaginatorFactory($this->getHttpRequest());
		$paginator = $paginatorFactory->getPaginator($this->blogEntryRepository->getPublishedCount(),
			Model\BlogEntryRepository::ITEMS_PER_PAGE);
		$articles = $this->blogEntryRepository->getList($paginator);

		$template = $this->getTemplate();
		$template->articles = $articles;
		$template->pageSubTitle = "";
	}
}
