<?php
namespace Mirin\AdminModule\Presenters;
use Nette;
use Mirin;
use Mirin\AdminModule\Components;

class EditCommentPresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var Mirin\Model\BlogAuthorRepository
	 */
	public $authorRepository;

	/**
	 * @inject
	 * @var Mirin\Model\BlogCommentRepository
	 */
	public $commentRepository;

	/**
	 * @var \Dibi\Row;
	 */
	private $currentUser;

	/**
	 * @var Mirin\Model\BlogComment
	 */
	private $comment;

	/**
	 * Check the log in user, and gets the current article from database.
	 * It's necessary to get the comment from db there, not till in render method.
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

		if (!($this->comment = $this->commentRepository->getById($id))) {
			throw new Nette\Application\BadRequestException("comment with id '$id' doesn't exist");
		}
	}

	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->comment = $this->comment;
		$template->currentUser = $this->currentUser;
		$template->subTitle = "Editace komentáře {$this->comment->id}";
	}

	/**
	 * @return Components\Menu
	 */
	protected function createComponentMenu()
	{
		return new Components\Menu(Components\Menu::ITEM_COMMENTS);
	}

	/**
	 * @return Components\CommentForm
	 */
	protected function createComponentCommentForm()
	{
		return new Components\CommentForm($this->commentRepository, $this->comment);
	}
}
