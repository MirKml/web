<?php
namespace Mirin\Presenters;

use Nette;
use Nette\Application\UI;
use Mirin\Model;
use Mirin\Components;

class BlogArticlePresenter extends UI\Presenter
{
	use Layout;

	/**
	 * @inject
	 * @var Model\BlogArticleRepository
	 */
	public $articleRepository;

	/**
	 * @inject
	 * @var Model\BlogCategoryRepository
	 */
	public $categoryRepository;

	/**
	 * @inject
	 * @var Model\BlogCommentRepository
	 */
	public $commentRepository;

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
		if (!($article = $this->articleRepository->getBySlug($slug))) {
			throw new Nette\Application\BadRequestException("no published article for '$slug'");
		}
		$this->article = $article;
	}

	public function renderDefault()
	{
		$relation = new Model\BlogArticleRelation([$this->article]);
		$relation->setCategoryRepository($this->categoryRepository);

		$template = $this->getTemplate();
		$template->article = $this->article;
		$template->articlesRelation = $relation;
		$template->commentsCount = $this->commentRepository->getCountForArticle($this->article);
		$template->pageSubTitle = $this->article->title;
	}

	protected function createComponentComments()
	{
		return new Components\ArticleComments($this->commentRepository, $this->article);
	}

	protected function createComponentPreviousNextArticle()
	{
		return new Components\PreviousNextArticle($this->articleRepository,
			$this->article);
	}
}
