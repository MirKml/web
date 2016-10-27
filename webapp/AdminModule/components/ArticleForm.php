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
		$articleData = $form->getValues();

		// update article
		if ($this->currentArticle) {
			try {
				$this->articleRepository->update($this->currentArticle->id, $articleData);
			} catch (\Dibi\Exception $e) {
				$form->addError("Nemohu aktualizovat záznam: " . $e->getMessage());
				return;
			}
			$this->flashMessage("Záznam aktualizován");
			$this->getPresenter()->redirect(":Admin:EditArticle:", $this->currentArticle->id);
		}

		// insert new article
		try {
			$newArticleId = $this->articleRepository->insert($articleData);
		} catch (\Dibi\Exception $e) {
			$form->addError("Nemohu vložit záznam: " . $e->getMessage());
			return;
		}
		$this->flashMessage("Záznam o článku {$articleData->title} uložen, můžete ho upravit");
		$this->getPresenter()->redirect(":Admin:EditArticle:", $newArticleId);
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
			->setRequired()
			->setDefaultValue($this->currentArticle
				? $this->currentArticle->posted->format("Y-m-d H:i")
				: date("Y-m-d H:i"));

		$form->addTextArea("mainText", "Text článku (wiki syntaxe)")
			->setRequired();
		if ($this->currentArticle) {
			$form["mainText"]->setDefaultValue($this->currentArticle->text);
		}

		$form->addSelect("status", "Stav")
			->setItems(["published", "new"], false)
			->setDefaultValue($this->currentArticle
				? $this->currentArticle->status
				: "new");

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

interface IArticleFormFactory
{
	/**
	 * @return ArticleForm
	 */
	public function create();
}