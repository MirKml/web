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
	 * @var Model\BlogComment
	 */
	private $commentPreview;

	/**
	 * @var string
	 */
	private $captchaImageUrl;

	/**
	 * @var Model\BlogArticle;
	 */
	private $article;

	/**
	 * @param string $slug article slug
	 * @throws Nette\Application\BadRequestException
	 */
	public function actionDefault($slug)
	{
		if (!($article = $this->blogArticleRepository->getBySlug($slug))) {
			throw new Nette\Application\BadRequestException("no published article for '$slug'");
		}
		$this->article = $article;
	}

	public function renderDefault()
	{
		$relation = new Model\BlogArticleRelation([$this->article]);
		$relation->setCategoryRepository($this->blogCategoryRepository);
		$comments = $this->blogCommentRepository->getByArticle($this->article);

		$template = $this->getTemplate();
		$template->article = $this->article;
		$template->articlesRelation = $relation;
		$template->comments = $comments;
		$template->commentPreview = $this->commentPreview;

		// it's necessary to set the captcha related controls there, because nette sets the old values
		// when the form is submitted and isn't valid, but we need to new captcha every time
		// event when the form isn't valid
		$captcha = self::getCaptcha();
		$form = $this->getComponent("commentForm");
		$form["hash"]->setValue($hash = $captcha->create());
		$form["code"]->setValue("");
		$template->captchaImageUrl = $captcha->getImage($hash);
		// better to set the form action to anchor, user don't have to scroll for preview and
		// for the error correction
		$form->setAction($form->getAction() . "#addComment");

		$template->pageSubTitle = $this->article->title;
	}

	public function handleCommentForm(UI\Form $form)
	{
		$this->commentPreview = Model\BlogCommentRepository::createPreviewComment([
			"article_id" => $this->article->id,
			"name" => $form["name"]->getValue(),
			"email" => $form["email"]->getValue(),
			"www" => $form["www"]->getValue() ?: null,
			"message" => $form["message"]->getValue(),
			"posted" => new \DateTime()
		]);
		if ($form->isSubmitted()->getName() == "preview") {
			// don't redirect, comment preview and form with current
			// values and save controls (captcha, save button) will be displayed
			return;
		}

		$captcha = self::getCaptcha();
		try {
			$captchaIsValid = $captcha->check($form["hash"]->getValue(), $form["code"]->getValue());
		} catch (\Exception $e) {
			$form->addError("Nepodařilo se ověrit captcha: " . $e->getMessage());
			return;
		}
		if (!$captchaIsValid) {
			$form->addError("Kód captcha se neshoduje.");
			return;
		}

		// save the comment
		$this->blogCommentRepository->insert($this->commentPreview);
		$this->flashMessage("Komentář byl uložen");
		$this->redirect("this#comments");
	}

	private static function getCaptcha() {
		return new \CaptchaHTTP("captcha.seznam.cz", 80);
	}

	protected function createComponentCommentForm()
	{
		$form = new UI\Form();
		$form->addText("name", "Jméno (povinné)")
			->setRequired("Vyplňte jméno");
		$form->addText("email", "Email (povinný)")
			->setRequired("Vyplňte email")
			->addRule(UI\Form::EMAIL, "Nerozpoznán platný email, zkuste to znovu");
		$form->addText("www", "Váš web");
		$form->addTextArea("message", "Tvůj komentář")
			->setRequired("Vyplňte komentář");
		$form->addSubmit("preview", "Náhled");
		$form->onSuccess[] = [$this, "handleCommentForm"];

		// for saving comment into db, these are conditionally visible in the template
		$form->addText("code", "Kód");
		$form->addHidden("hash");
		$form->addSubmit("save", "Ulož");

		return $form;
	}
}
