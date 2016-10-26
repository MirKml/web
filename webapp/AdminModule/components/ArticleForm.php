<?php
namespace Mirin\AdminModule\Components;
use Nette\Application\UI;
use Mirin\Model;
use Mirin\AdminModule;

class ArticleForm extends UI\Control
{
	/**
	 * @var AdminModule\Model\ArticleRepository
	 */
	private $articleRepository;

	/**
	 * @var Model\BlogAuthorRepository;
	 */
	private $authorRepository;

	/**
	 * @var \Dibi\Row
	 */
	private $currentArticle;

	public function __construct(AdminModule\Model\ArticleRepository $articleRepository,
		Model\BlogAuthorRepository $authorRepository)
	{
		$this->articleRepository = $articleRepository;
		$this->authorRepository = $authorRepository;
		parent::__construct();
	}

	public function setCurrentArticle(\Dibi\Row $article)
	{
		$this->currentArticle = $article;
	}

	public function processForm(UI\Form $form)
	{
		if ($this->currentArticle) {
			try {
				$this->articleRepository->update($this->currentArticle->id,
					$form->getValues());
			} catch (\Dibi\Exception $e) {
				$form->addError("Nemohu aktualizovat záznam: " . $e->getMessage());
				return;
			}
			$this->flashMessage("Záznam aktualizován");
			$this->getPresenter()->redirect(":Admin:EditArticle:", $this->currentArticle->id);
		}
	}

	protected function createComponentInnerForm()
	{
		$form = new UI\Form();
		$form->addText("title", "Titulek")
			->setRequired();
		if ($this->currentArticle) {
			$form["title"]->setDefaultValue($this->currentArticle->title);
		}

		$form->addSelect("author", "Autor", $this->authorRepository->getAllForSelectBox())
			->setRequired();
		if ($this->currentArticle) {
			$form["author"]->setDefaultValue($this->currentArticle->author_id);
		}

		$form->addText("posted", "Vytvořeno")
			->setRequired();
		if ($this->currentArticle) {
			$form["posted"]->setDefaultValue($this->currentArticle->posted->format("Y-m-d H:i"));
		}

		$form->addTextArea("mainText", "Text článku (wiki syntaxe)")
			->setRequired();
		if ($this->currentArticle) {
			$form["mainText"]->setDefaultValue($this->currentArticle->text);
		}

		$form->addSelect("status", "Stav")
			->setItems(["published", "new"], false);
		if ($this->currentArticle) {
			$form["status"]->setDefaultValue($this->currentArticle->status);
		}

		$form->addSubmit("save", "Ulož");

		$form->onSuccess[] = [$this, "processForm"];
		return $form;
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile(__DIR__ . "/templates/articleForm.latte");
		$template->render();
	}
}