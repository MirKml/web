<?php
namespace Mirin\Presenters;
use Nette;

class AboutPresenter extends Nette\Application\UI\Presenter
{
	use Layout;

	public function renderDefault()
	{
		$this->getTemplate()->pageSubTitle = "Informace";
	}
}
