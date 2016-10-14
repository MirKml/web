<?php
namespace Mirin\Presenters;
use Nette;
use Nette\Application\UI;
use Mirin\Model;

class BlogArticlePresenter extends UI\Presenter
{

	/**
	 * @inject
	 * @var Model\BlogArticleRepository
	 */
	public $blogArticleRepository;

	/**
	 * @inject
	 * @var Model\BlogCategoryRepository
	 */
	public $blogCategoryRepository;

	/**
	 * @inject
	 * @var Model\BlogCommentRepository
	 */
	public $blogCommentRepository;

	/**
	 * @param string $slug article slug
	 * @throws Nette\Application\BadRequestException
	 */
	public function renderDefault($slug)
	{
		if (!($article = $this->blogArticleRepository->getBySlug($slug))) {
			throw new Nette\Application\BadRequestException("no published article for '$slug'");
		}
		$relation = new Model\BlogArticleRelation([$article]);
		$relation->setCategoryRepository($this->blogCategoryRepository);
		$comments = $this->blogCommentRepository->getByArticle($article);

		$template = $this->getTemplate();
		$template->article = $article;
		$template->articlesRelation = $relation;
		$template->comments = $comments;
		$template->pageSubTitle = $article->title;
	}

	protected function handleCommentForm(UI\Form $form)
	{

	}

	protected function createComponentCommentForm()
	{
		$form = new UI\Form();
		$form->addText("name", "jméno")
			->setRequired("Vyplňte jméno");
		$form->addText("email", "email")
			->setRequired("Vyplňte email");
		$form->addText("www", "www");
		$form->addTextArea("message");
		$form->addSubmit('send', 'Vlož');
		$form->onSuccess[] = [$this, "handleCommentForm"];
		return $form;
	}
}
