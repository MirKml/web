<?php
namespace Mirin\AdminModule\Presenters;
use Nette;
use Mirin;
use Mirin\AdminModule\Components;

class CommentsPresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @var Nette\Utils\Paginator
	 */
	private $paginator;

	/**
	 * @inject
	 * @var Mirin\Model\BlogCommentRepository
	 */
	public $commentsRepository;

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
		$this->paginator = $paginatorFactory->getPaginator($this->commentsRepository->getAllCount(), 20);
		$comments = $this->commentsRepository->getForPage($this->paginator);

		$template = $this->getTemplate();
		$template->comments = $comments;
		$template->currentUser = $currentUser;
		$template->subTitle = "Komentáře";
	}

	protected function createComponentPagination()
	{
		return new Components\Pagination($this->paginator, $this->link("this"));
	}

	protected function createComponentMenu()
	{
		return new Components\Menu(Components\Menu::ITEM_COMMENTS);
	}
}