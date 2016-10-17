<?php
namespace Mirin\Presenters;
use Nette;
use Mirin\Model;
use Mirin\Components;

class BlogCategoryPresenter extends Nette\Application\UI\Presenter
{
	use Layout;

	/**
	 * @inject
	 * @var Model\BlogCategoryRepository
	 */
	public $categoryRepository;

	/**
	 * @inject
	 * @var Model\BlogArticleRepository
	 */
	public $articleRepository;

	/**
	 * @var Nette\Utils\Paginator
	 */
	private $paginator;

	/**
	 * @param string $slug
	 */
	public function renderDefault($slug)
	{
		if (!($category = $this->categoryRepository->getBySlug($slug))) {
			throw new Nette\Application\BadRequestException("category for '$slug' doesn't exist");
		}

		$paginatorFactory = new PaginatorFactory($this->getHttpRequest());
		$this->paginator = $paginatorFactory->getPaginator($this->articleRepository->getInCategoryPublishedCount($category),
			Model\BlogArticleRepository::ITEMS_IN_CATEGORY_PER_PAGE);
		$articles = $this->articleRepository->getInCategoryList($category, $this->paginator);
		$relation = new Model\BlogArticleRelation($articles);
		$relation->setCategoryRepository($this->categoryRepository);

		$template = $this->getTemplate();
		$template->category = $category;
		$template->articles = $articles;
		$template->articlesRelation = $relation;
		$template->monthFormatter = new \IntlDateFormatter(null, null, null, null, null, "MMMM");
		$template->pageSubTitle = "Seznam kategorií";
	}

	protected function createComponentPagination()
	{
		return new Components\Pagination($this->paginator, $this->link("this"));
	}
}