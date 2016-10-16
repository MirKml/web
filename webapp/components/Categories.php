<?php
namespace Mirin\Components;
use Nette;
use Mirin\Model;

class Categories extends Nette\Application\UI\Control
{

	/**
	 * @var Model\BlogCategoryRepository
	 */
	private $categoryRepository;

	public function __construct(Model\BlogCategoryRepository $categoryRepository)
	{
		$this->categoryRepository = $categoryRepository;
		parent::__construct();
	}

	public function render()
	{
		if (!($categories = $this->categoryRepository->getAll())) {
			return;
		}

		$template = $this->getTemplate();
		$template->categories = $categories;
		$template->setFile(__DIR__ . "/templates/categories.latte");
		$template->render();
	}
}

interface ICategoriesFactory
{
	/** @return Categories */
	public function create();
}
