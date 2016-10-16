<?php
namespace Mirin\Presenters;
use Nette;

class BlogCategoryPresenter extends Nette\Application\UI\Presenter
{
	use Layout;

	public function renderDefault($slug)
	{

		$template = $this->getTemplate();
		$template->pageSubTitle = "Seznam kategorií";
	}
}