<?php
namespace Mirin\Presenters;
use Nette;
use Mirin\Model;

class RssPresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @inject
	 * @var Model\BlogArticleRepository
	 */
	public $articleRepository;

	public function renderDefault()
	{
		$articles = $this->articleRepository->getRssList();

		$template = $this->getTemplate();
		$template->articles = $articles;
		$template->latestArticlePosted = $articles[0]->posted;
	}

}
