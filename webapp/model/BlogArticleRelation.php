<?php
namespace Mirin\Model;

class BlogArticleRelation
{
	/**
	 * @var BlogArticle[]
	 */
	private $entries;

	/**
	 * @var array
	 */
	private $identifiers;

	/**
	 * @var BlogCategoryRepository
	 */
	private $categoryRepository;

	/**
	 * @var array
	 */
	private $categories;

	public function __construct(array $entries)
	{
		$this->entries = $entries;
	}

	public function setCategoryRepository(BlogCategoryRepository $repository)
	{
		$this->categoryRepository = $repository;
	}

	/**
	 * get list of IDs for entries
	 * @return array
	 */
	private function getIdentifiers()
	{
		if ($this->identifiers) return $this->identifiers;
		foreach ($this->entries as $entry) {
			$identifiers[] = $entry->id;
		}
		return $this->identifiers = $identifiers;
	}

	/**
	 * @param BlogArticle $article
	 * @return array
	 */
	public function getCategoriesForArticle(BlogArticle $article)
	{
		if (!$this->categories) {
			$this->categories = $this->categoryRepository->getByArticles($this->getIdentifiers());
		}

		return isset($this->categories[$article->id])
			? $this->categories[$article->id]
			: [];
	}
}