<?php
namespace Mirin\AdminModule\Components;
use Nette\Application\UI;
use Mirin\Model;

class CommentForm extends UI\Control
{
	/**
	 * @var Model\BlogCommentRepository
	 */
	private $commentRepository;

	/**
	 * @var Model\BlogComment
	 */
	private $comment;

	public function __construct(Model\BlogCommentRepository $commentRepository,
		Model\BlogComment $comment)
	{
		$this->commentRepository = $commentRepository;
		$this->comment = $comment;
		parent::__construct();
	}

	public function processForm(UI\Form $form)
	{
		$commentData = $form->getValues();

		// update article
		try {
			$this->commentRepository->update($this->comment->id, $commentData);
		} catch (\Dibi\Exception $e) {
			$form->addError("Nemohu aktualizovat záznam: " . $e->getMessage());
			return;
		}
		$this->flashMessage("Záznam aktualizován");
		$this->getPresenter()->redirect(":Admin:EditComment:", $this->comment->id);
	}

	protected function createComponentInnerForm()
	{
		$form = new UI\Form();
		$form->addTextArea("message", "Komentář")
			->setRequired()
			->setDefaultValue($this->comment->message);

		$form->addText("visitor", "Komentující")
			->setRequired()
			->setDefaultValue($this->comment->name);

		$form->addText("email", "Email komentujícího")
			->setRequired()
			->setDefaultValue($this->comment->email);

		$form->addText("www", "Web komentujícího")
			->setDefaultValue($this->comment->www);

		$form->addText("posted", "Vloženo")
			->setRequired()
			->setDefaultValue($this->comment->posted->format("Y-m-d H:i"));

		$form->addSubmit("save", "Ulož");

		$form->onSuccess[] = [$this, "processForm"];
		return $form;
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . "/templates/commentForm.latte");
		$template->render();
	}
}

