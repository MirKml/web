<?php
namespace Mirin\Components;
use Nette;
use Mirin\Model;

class PreviousNextArticle extends Nette\Application\UI\Control
{
	/**
	 * @var Model\BlogArticleRepository
	 */
	private $articleRepository;
	/**
	 * @var Model\BlogArticle
	 */
	private $currentArticle;

	public function __construct(Model\BlogArticleRepository $articleRepository,
		Model\BlogArticle $currentArticle)
	{
		$this->articleRepository = $articleRepository;
		$this->currentArticle = $currentArticle;
		parent::__construct();
	}

	public function render()
	{
		$previous = $this->articleRepository->getPrevious($this->currentArticle);
		$next = $this->articleRepository->getNext($this->currentArticle);
		if (!$previous && !$next) return;

		$template = $this->getTemplate();
		$template->previous = $previous;
		$template->next = $next;
		$template->setFile(__DIR__ . "/templates/previousNextArticle.latte");
		$template->render();
	}
}