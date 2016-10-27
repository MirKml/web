<?php
namespace Mirin\AdminModule\Presenters;
use Nette;
use Mirin;
use Mirin\Model;

/**
 * Preview for the article text.
 * It's used for articles, which aren't published already.
 */
class PreviewArticlePresenter extends Nette\Application\UI\Presenter
{
	use Mirin\Presenters\Layout;

	/**
	 * @inject
	 * @var Model\BlogArticleRepository
	 */
	public $articleRepository;

	public function renderDefault($slug)
	{
		if (!($article = $this->articleRepository->getForPreview($slug))) {
			throw new Nette\Application\BadRequestException("no article '$slug' available");
		}

		$template = $this->getTemplate();
		$template->article = $article;
		$template->pageSubTitle = "Náhled článku";
	}
}