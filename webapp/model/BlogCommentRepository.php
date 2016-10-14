<?php
namespace Mirin\Model;

class BlogCommentRepository
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
	 * @param BlogArticle $article
	 * @return array
	 */
	public function getByArticle(BlogArticle $article)
	{
		foreach ($this->db->fetchAll("select *
			from comment
			where article_id = %i", $article->id) as $commentRow) {

			$comments[] = new BlogComment($commentRow);
		};
		return isset($comments) ? $comments : [];
	}
}