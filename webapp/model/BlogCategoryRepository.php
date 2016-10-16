<?php
namespace Mirin\Model;

class BlogCategoryRepository
{
	/**
	 * @var \Dibi\Connection
	 */
	private $db;

	public function __construct(\Dibi\Connection $db)
	{
		$this->db = $db;
	}

	/**
	 * @param array $entryIDs
	 * @return array
	 */
	public function getByArticles(array $entryIDs)
	{
		return $this->db->query("select category.*, categoryarticle.article_id
			from category
			inner join categoryarticle on categoryarticle.category_id = category.id 
				and categoryarticle.article_id IN (%i)", $entryIDs)
			->fetchAssoc("article_id[]");
	}

	public function getAll()
	{
		return $this->db->fetchAll("select * from category");
	}
}