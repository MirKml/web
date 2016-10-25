<?php
namespace Mirin\AdminModule\Presenters;
use Nette;

class ThemePresenter extends Nette\Application\UI\Presenter
{
	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->subTitle = "theme overview";
	}
}
