<?php
namespace Mirin\Presenters;
use Mirin\Components;

trait Layout
{
	/**
	 * @inject
	 * @var Components\ICategoriesFactory
	 */
	public $categoryFormFactory;

	protected function createComponentCategories()
	{
		return $this->categoryFormFactory->create();
	}
}

