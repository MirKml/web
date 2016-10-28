<?php
namespace Mirin\AdminModule\Presenters;
use Mirin\Model;
use Nette;

class DeleteCommentPresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @inject
	 * @var Model\BlogCommentRepository
	 */
	public $commentRepository;

	/**
	 * @param int $id comment id
	 */
	public function actionDefault($id)
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect("LogIn:");
		}

		$this->commentRepository->delete($id);
		$this->flashMessage("Komentář '$id' smazán.");
		$this->redirect("Comments:");
	}
}
