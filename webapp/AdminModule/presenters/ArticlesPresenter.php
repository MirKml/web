<?php
namespace Mirin\AdminModule\Presenters;
use Nette;

class ArticlesPresenter extends Nette\Application\UI\Presenter
{

	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->subTitle = "články";
	}
}