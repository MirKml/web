<?php
namespace Mirin\AdminModule\Presenters;
use Nette;
use Mirin;
use Mirin\AdminModule\Components;

class ArticlesPresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @var Nette\Utils\Paginator
	 */
	private $paginator;

	/**
	 * @inject
	 * @var Mirin\AdminModule\Model\ArticleRepository
	 */
	public $articleRepository;

	/**
	 * @inject
	 * @var Mirin\Model\BlogAuthorRepository
	 */
	public $authorRepository;

	public function renderDefault()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect("LogIn:");
		}
		$currentUser = $this->authorRepository->getById($this->getUser()->getId());

		$paginatorFactory = new Mirin\Presenters\PaginatorFactory($this->getHttpRequest());
		$this->paginator = $paginatorFactory->getPaginator($this->articleRepository->getAllCount(),
			Mirin\AdminModule\Model\ArticleRepository::ITEMS_PER_PAGE);
		$articles = $this->articleRepository->getItems($this->paginator);

		$template = $this->getTemplate();
		$template->articles = $articles;
		$template->currentUser = $currentUser;
		$template->subTitle = "články";
	}

	protected function createComponentPagination()
	{
		return new Components\Pagination($this->paginator, $this->link("this"));
	}

	protected function createComponentMenu()
	{
		return new Components\Menu(Components\Menu::ITEM_ARTICLES);
	}
}