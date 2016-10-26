<?php
namespace Mirin\AdminModule\Presenters;
use Nette;
use Mirin;
use Mirin\AdminModule\Components;

class EditArticlePresenter extends Nette\Application\UI\Presenter
{

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

	/**
	 * @var \Dibi\Row;
	 */
	private $currentUser;

	/**
	 * @var \Dibi\Row;
	 */
	private $article;

	/**
	 * Check the log in user, and gets the current article from database.
	 * It's necessary to get the article from db there, not till in render method.
	 * Because when the form is submitted, render method isn't called before form component factory
	 *
	 * @param int $id article id
	 * @throws Nette\Application\BadRequestException
	 */
	public function actionDefault($id)
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect("LogIn:");
		}
		$this->currentUser = $this->authorRepository->getById($this->getUser()->getId());

		if (!($this->article = $this->articleRepository->getById($id))) {
			throw new Nette\Application\BadRequestException("article with id '$id' doesn't exist");
		}
	}

	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->article = $this->article;
		$template->currentUser = $this->currentUser;
		$template->subTitle = "editace článku {$this->article->title}";
	}

	/**
	 * @return Components\Menu
	 */
	protected function createComponentMenu()
	{
		return new Components\Menu(Components\Menu::ITEM_ARTICLES);
	}

	/**
	 * @return Components\ArticleForm
	 */
	protected function createComponentArticleForm()
	{
		$component = new Components\ArticleForm($this->articleRepository, $this->authorRepository);
		$component->setCurrentArticle($this->article);
		return $component;
	}
}
