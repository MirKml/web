<?php
namespace Mirin\Presenters;
use Nette;
use Mirin\Components;
use Mirin\Model;

class IndexPresenter extends Nette\Application\UI\Presenter
{
	use Layout;

	/**
	 * @var Nette\Utils\Paginator
	 */
	private $paginator;

	/**
	 * @inject
	 * @var Model\BlogArticleRepository
	 */
	public $articleRepository;

	/**
	 * @inject
	 * @var Model\BlogCategoryRepository
	 */
	public $categoryRepository;

	public function renderDefault()
	{
		$paginatorFactory = new PaginatorFactory($this->getHttpRequest());
		$this->paginator = $paginatorFactory->getPaginator($this->articleRepository->getPublishedCount(),
			Model\BlogArticleRepository::ITEMS_PER_PAGE);
		$articles = $this->articleRepository->getList($this->paginator);
		$relation = new Model\BlogArticleRelation($articles);
		$relation->setCategoryRepository($this->categoryRepository);

		$template = $this->getTemplate();
		$template->articles = $articles;
		$template->articlesRelation = $relation;
		$template->pageSubTitle = "";
	}

	protected function createComponentPagination()
	{
		return new Components\Pagination($this->paginator, $this->link("this"));
	}

}
