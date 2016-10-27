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

	/**
	 * Gets category IDs for particular article
	 * It's used in the article administration
	 * @param int $articleId
	 * @return array
	 */
	public function getIDsForArticle($articleId)
	{
		return $this->db->query("select categoryarticle.article_id, category.*
			from categoryarticle
			inner join category on categoryarticle.category_id = category.id 
			where categoryarticle.article_id = %i", $articleId)
			->fetchAssoc("[]=id");
	}

	/**
	 * Gets category names with IDs.
	 * It's used in the article administration.
	 * @return array
	 */
	public function getNamesWithIDs()
	{
		return $this->db->query("select id,name from category")
			->fetchAssoc("id|=name");
	}

	public function getAll()
	{
		return $this->db->fetchAll("select * from category");
	}

	/**
	 * @param string $slug
	 * @return object|bool
	 */
	public function getBySlug($slug)
	{
		return $this->db->fetch("select * from category where titleURL = %s", $slug);
	}
}