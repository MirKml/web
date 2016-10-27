<?php
namespace Mirin\AdminModule\Presenters;
use Nette;
use Mirin;
use Mirin\AdminModule\Components;

class NewArticlePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var Components\IArticleFormFactory
	 */
	public $articleFormFactory;

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

		$template = $this->getTemplate();
		$template->currentUser = $currentUser;
		$template->subTitle = "Nový článek";
	}

	/**
	 * @return Components\ArticleForm
	 */
	protected function createComponentArticleForm()
	{
		return $this->articleFormFactory->create();
	}

	/**
	 * @return Components\Menu
	 */
	protected function createComponentMenu()
	{
		return new Components\Menu(Components\Menu::ITEM_ARTICLES);
	}
}
