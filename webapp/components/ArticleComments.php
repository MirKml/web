<?php
namespace Mirin\Components;
use Mirin\Model;
use Nette\Application\UI;

class ArticleComments extends UI\Control
{
	/**
	 * @var Model\BlogCommentRepository;
	 */
	private $commentRepository;

	/**
	 * @var Model\BlogArticle
	 */
	private $article;

	/**
	 * @var Model\BlogComment;
	 */
	private $commentPreview;

	/**
	 * @var string
	 */
	private $captchaImageUrl;

	public function __construct(Model\BlogCommentRepository $commentRepository,
		Model\BlogArticle $article)
	{
		$this->commentRepository = $commentRepository;
		$this->article = $article;
		parent::__construct();
	}

	public function render()
	{
		$comments = $this->commentRepository->getByArticle($this->article);
		$template = $this->getTemplate();
		$template->comments = $comments;
		$commentsOpen = true;

		if (new \DateTime("-41 days") > $this->article->posted) {
			$commentsOpen = false;
			goto renderTemplate;
		}

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

		renderTemplate:
		$template->commentsOpen = $commentsOpen;
		$template->setFile(__DIR__ . "/templates/articleComments.latte");
		$template->render();
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
		$this->commentRepository->insert($this->commentPreview);
		$presenter = $this->getPresenter();
		$this->flashMessage("Komentář byl uložen");
		$presenter->redirect("this#comments");
	}

	private static function getCaptcha() {
		return new \CaptchaHTTP("captcha.seznam.cz", 80);
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentCommentForm()
	{
		$form = new UI\Form();
		$form->addText("name", "Jméno (povinné)")
			->setRequired("Vyplňte jméno");
		$form->addText("email", "Email (povinný)")
			->setRequired("Vyplňte email")
			->addRule(UI\Form::EMAIL, "Nerozpoznán platný email, zkuste to znovu");
		$form->addText("www", "Váš web");
		$form->addTextArea("message", "Tvůj komentář (povoleno <a href>, <pre>)")
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